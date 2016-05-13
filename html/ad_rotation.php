<?php
session_start();

//$domain_prefix = "http://agn.ph";
$domain_prefix = "";
$source_prefix = "/images/banner_rotation/";
$mapping = array(
        array("shikaro89826.png", "/gallery/post/?search=vaporeon&feature=89826"),
        array("sefuart101447.png", "/gallery/post/?search=flareon&feature=101447"),
        array("watermelon87100.png", "/gallery/post/?search=flygon&feature=87100"),
        array("pienji101284.png", "/gallery/post/?search=goodra&feature=101284"),
        array("agnphbannerchangingseasonsmeranceprpr.png", "/fics/"),
        array("agnphbannerchangingseasonsmeranceprpr.png", "/fics/"),
        array("agnphchangingseasonssefuartbeachv2.png", "/fics/"),
        array("agnphchangingseasonssefuartbeachv2.png", "/fics/"),
        array("agnphchangingseasonsshikarokrokodile.png", "/fics/"),
        array("agnphchangingseasonsshikarokrokodile.png", "/fics/"),
    );

if (isset($_GET['image'])) {
    $index = rand(0, sizeof($mapping) - 1);
    $img_src = $domain_prefix.$source_prefix.$mapping[$index][0];
    $link = $domain_prefix.$mapping[$index][1];

    $_SESSION['ad_link'] = $link;
    header("Location: $img_src");
} else if (isset($_GET['link'])) {
    if (isset($_SESSION['ad_link'])) {
        header("Location: ".$_SESSION['ad_link']);
        echo $_SESSION['ad_link'];
        unset($_SESSION['ad_link']);
    } else {
        // Go to default link.
        $default_link = "/gallery/post/";
        header("Location: $default_link");
    }
} else {
    echo "<a href='/rotation/link/'><img src='/rotation/image/' /></a>";
}

?>
