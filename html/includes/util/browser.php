<?php
// Utility functions for managing browsers/user-agents/bots.

function IsRealUser() {
    if (isset($_SESSION['user_is_not_bot'])) return $_SESSION['user_is_not_bot'];
    /*
    $bots = array(
        "008",
        "ABACHOBot",
        "Accoona-AI-Agent",
        "AddSugarSpiderBot",
        "AnyApexBot",
        "Arachmo",
        "B-l-i-t-z-B-O-T",
        "Baiduspider",
        "BecomeBot",
        "BeslistBot",
        "BillyBobBot",
        "Bimbot",
        "Bingbot",
        "BlitzBOT",
        "boitho.com-dc",
        "boitho.com-robot",
        "btbot",
        "CatchBot",
        "Cerberian Drtrs",
        "Charlotte",
        "ConveraCrawler",
        "cosmos",
        "Covario IDS",
        "DataparkSearch",
        "DiamondBot",
        "Discobot",
        "Dotbot",
        "EARTHCOM.info",
        "EmeraldShield.com WebBot",
        "envolk[ITS]spider",
        "EsperanzaBot",
        "Exabot",
        "FAST Enterprise Crawler",
        "FAST-WebCrawler",
        "FDSE robot",
        "FindLinks",
        "FurlBot",
        "FyberSpider",
        "g2crawler",
        "Gaisbot",
        "GalaxyBot",
        "genieBot",
        "Gigabot",
        "Girafabot",
        "Googlebot",
        "Googlebot-Image",
        "Google Page Speed Insights",
        "GurujiBot",
        "HappyFunBot",
        "hl_ftien_spider",
        "Holmes",
        "htdig",
        "iaskspider",
        "ia_archiver",
        "iCCrawler",
        "ichiro",
        "igdeSpyder",
        "IRLbot",
        "IssueCrawler",
        "Jaxified Bot",
        "Jyxobot",
        "KoepaBot",
        "L.webis",
        "LapozzBot",
        "Larbin",
        "LDSpider",
        "LexxeBot",
        "Linguee Bot",
        "LinkWalker",
        "lmspider",
        "lwp-trivial",
        "mabontland",
        "magpie-crawler",
        "Mediapartners-Google",
        "MJ12bot",
        "MLBot",
        "Mnogosearch",
        "mogimogi",
        "MojeekBot",
        "Moreoverbot",
        "Morning Paper",
        "msnbot",
        "MSRBot",
        "MVAClient",
        "mxbot",
        "NetResearchServer",
        "NetSeer Crawler",
        "NewsGator",
        "NG-Search",
        "nicebot",
        "noxtrumbot",
        "Nusearch Spider",
        "NutchCVS",
        "Nymesis",
        "obot",
        "oegp",
        "omgilibot",
        "OmniExplorer_Bot",
        "OOZBOT",
        "Orbiter",
        "PageBitesHyperBot",
        "Peew",
        "polybot",
        "Pompos",
        "PostPost",
        "Psbot",
        "PycURL",
        "Qseero",
        "Radian6",
        "RAMPyBot",
        "RufusBot",
        "SandCrawler",
        "SBIder",
        "ScoutJet",
        "Scrubby",
        "SearchSight",
        "Seekbot",
        "semanticdiscovery",
        "semrush",
        "Sensis Web Crawler",
        "SEOChat::Bot",
        "SeznamBot",
        "Shim-Crawler",
        "ShopWiki",
        "Shoula robot",
        "silk",
        "Sitebot",
        "Snappy",
        "sogou spider",
        "Sosospider",
        "Speedy Spider",
        "Sqworm",
        "StackRambler",
        "suggybot",
        "SurveyBot",
        "SynooBot",
        "Teoma",
        "TerrawizBot",
        "TheSuBot",
        "Thumbnail.CZ robot",
        "TinEye",
        "truwoGPS",
        "TurnitinBot",
        "TweetedTimes Bot",
        "TwengaBot",
        "updated",
        "Urlfilebot",
        "Vagabondo",
        "VoilaBot",
        "Vortex",
        "voyager",
        "VYU2",
        "webcollage",
        "Websquash.com",
        "wf84",
        "WoFindeIch Robot",
        "WomlpeFactory",
        "Xaldon_WebSpider",
        "yacy",
        "Yahoo! Slurp",
        "Yahoo! Slurp China",
        "YahooSeeker",
        "YahooSeeker-Testing",
        "YandexBot",
        "YandexImages",
        "YandexMetrika",
        "YandexMobileBot",
        "Yasaklibot",
        "Yeti",
        "YodaoBot",
        "yoogliFetchAgent",
        "YoudaoBot",
        "Zao",
        "Zealbot",
        "zspider",
        "ZyBorg",
        );
    $pattern = strtolower('/(' . implode('|', $bots) .')/');
    */
    $pattern = "/(008|abachobot|accoona-ai-agent|addsugarspiderbot|anyapexbot|arachmo|b-l-i-t-z-b-o-t|baiduspider|becomebot|beslistbot|billybobbot|bimbot|bingbot|blitzbot|boitho.com-dc|boitho.com-robot|btbot|catchbot|cerberian drtrs|charlotte|converacrawler|cosmos|covario ids|dataparksearch|diamondbot|discobot|dotbot|earthcom.info|emeraldshield.com webbot|envolk[its]spider|esperanzabot|exabot|fast enterprise crawler|fast-webcrawler|fdse robot|findlinks|furlbot|fyberspider|g2crawler|gaisbot|galaxybot|geniebot|gigabot|girafabot|googlebot|googlebot-image|google page speed insights|gurujibot|happyfunbot|hl_ftien_spider|holmes|htdig|iaskspider|ia_archiver|iccrawler|ichiro|igdespyder|irlbot|issuecrawler|jaxified bot|jyxobot|koepabot|l.webis|lapozzbot|larbin|ldspider|lexxebot|linguee bot|linkwalker|lmspider|lwp-trivial|mabontland|magpie-crawler|mediapartners-google|mj12bot|mlbot|mnogosearch|mogimogi|mojeekbot|moreoverbot|morning paper|msnbot|msrbot|mvaclient|mxbot|netresearchserver|netseer crawler|newsgator|ng-search|nicebot|noxtrumbot|nusearch spider|nutchcvs|nymesis|obot|oegp|omgilibot|omniexplorer_bot|oozbot|orbiter|pagebiteshyperbot|peew|polybot|pompos|postpost|psbot|pycurl|qseero|radian6|rampybot|rufusbot|sandcrawler|sbider|scoutjet|scrubby|searchsight|seekbot|semanticdiscovery|semrush|sensis web crawler|seochat::bot|seznambot|shim-crawler|shopwiki|shoula robot|silk|sitebot|snappy|sogou spider|sosospider|speedy spider|sqworm|stackrambler|suggybot|surveybot|synoobot|teoma|terrawizbot|thesubot|thumbnail.cz robot|tineye|truwogps|turnitinbot|tweetedtimes bot|twengabot|updated|urlfilebot|vagabondo|voilabot|vortex|voyager|vyu2|webcollage|websquash.com|wf84|wofindeich robot|womlpefactory|xaldon_webspider|yacy|yahoo! slurp|yahoo! slurp china|yahooseeker|yahooseeker-testing|yandexbot|yandeximages|yandexmetrika|yandexmobilebot|yasaklibot|yeti|yodaobot|yooglifetchagent|youdaobot|zao|zealbot|zspider|zyborg)/";
    $matches = array();
    $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $numMatches = preg_match($pattern, $useragent, $matches);
    if($numMatches > 0) {
        // Found a bot string.
        $_SESSION['user_is_not_bot'] = false;
        return false;
    }
    if (strpos($useragent, "bot") !== FALSE || strpos($useragent, "spider") !== FALSE) {
        // Found a bot string.
        $_SESSION['user_is_not_bot'] = false;
        return false;
    }
    // Check for banned useragents.
    if (IsBlacklistedURLVisitor()) {
        // Found a bot string.
        $_SESSION['user_is_not_bot'] = false;
        return false;
    }
    $_SESSION['user_is_not_bot'] = true;
    return true;
}

function IsBlacklistedURLVisitor() {
    $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
    include(SITE_ROOT."includes/util/blacklisted_visit_urls.php");
    $pattern = "/(".implode("|", array_map(function($s) { return str_replace("/", "\\/", $s); }, $BLACKLISTED_USER_AGENT_REGEXES)).")/";
    $matches = array();
    $numMatches = preg_match($pattern, $useragent, $matches);
    return $numMatches > 0;
}

function IsBlacklistedBot() {
    $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $pattern = "/(yandex|baidu|semrush)/";
    $matches = array();
    $numMatches = preg_match($pattern, $useragent, $matches);
    return $numMatches > 0;
}

?>