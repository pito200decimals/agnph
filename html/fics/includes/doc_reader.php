<?php

class DocReader {

    function ReadDocument($filepath) {
        $this->data = null;
        $zip = new ZipArchive();
        $dataFile = "word/document.xml";
        // Open received archive file
        if (true === $zip->open($filepath)) {
            // If done, search for the data file in the archive
            if (($dataIndex = $zip->locateName($dataFile)) !== false) {
                // If found, read it to the string
                $this->data = $zip->getFromIndex($dataIndex);
            }
            $zip->close();
        }
    }

    function ParseDocumentToHTML() {
        if ($this->data == null) return null;
        $xmlDoc = new DOMDocument();
        if ($xmlDoc->loadXML($this->data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING) === FALSE) return null;
        return $this->ParseElementWithStyle($xmlDoc);
    }
    
    function ParseElementWithStyle($node) {
        $nodeName = $node->nodeName;
        if ($nodeName == "w:p") {
            $tag = "p";
        } else if ($nodeName == "w:r") {
            $tag = "span";
        } else if ($nodeName == "w:t") {
            return $node->textContent;
        } else if ($nodeName == "w:br") {
            return "<br />";
        } else if ($nodeName == "w:hr") {
            return "<hr />";
        } else {
            // Default case, just parse inner tag elements.
            $html = "";
            if ($node->hasChildNodes()) {
                foreach ($node->childNodes as $child) {
                    $html .= $this->ParseElementWithStyle($child);
                }
            }
            return $html;
        }
        // Process span and p tags.
        $style = "";
        $innerHTML = "";
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $rChild) {
                if ($rChild->nodeName == "w:pPr" || $rChild->nodeName == "w:rPr") {
                    $style = $this->ParseStylingRecursive($rChild);
                } else {
                    $innerHTML .= $this->ParseElementWithStyle($rChild);
                }
            }
        }
        if (strlen($style) > 0) {
            return "<$tag style='$style;'>$innerHTML</$tag>";
        } else if ($tag == "p") {
            return "<p>$innerHTML</p>";
        } else {
            return $innerHTML;
        }
    }
    
    function ParseStylingRecursive($node) {
        if ($node->nodeName == "w:b") return "font-weight: bold";
        if ($node->nodeName == "w:i") return "font-style: italic";
        if ($node->nodeName == "w:u") return "text-decoration: underline";
        if ($node->nodeName == "w:color") return "color: #".$node->getAttribute("w:val");
        if ($node->nodeName == "w:pStyle" || $node->nodeName == "w:rStyle") {
            $type = $node->getAttribute("w:val");
            if ($type == "Heading1") {
                return "font-size: 200%";
            } else if ($type == "Heading2") {
                return "font-size: 150%";
            } else if ($type == "Code") {
                return "font-family: monospace";
            } else if ($type == "Strong") {
                return "font-weight: bold";
            } else if ($type == "Emphasis") {
                return "font-style: italic";
            } else if ($type == "SubtleEmphasis") {
                return "font-style: italic";
            }
        }
        if ($node->nodeName == "w:sz") return "font-size: ".($node->getAttribute("w:val")/2)."pt";
        if ($node->nodeName == "w:rFonts") return "font-family: ".$node->getAttribute("w:ascii");
        
        if ($node->nodeName == "w:pPr" || $node->nodeName == "w:rPr") {
            $styles = array();
            if ($node->hasChildNodes()) {
                foreach ($node->childNodes as $child) {
                    $styles[] = $this->ParseStylingRecursive($child);
                }
            }
            return implode("; ", array_filter($styles, "strlen"));
        }
        return "";
    }
}
?>