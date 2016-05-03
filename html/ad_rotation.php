<?php
session_start();

//$domain_prefix = "http://agn.ph";
$domain_prefix = "";
$mapping = array(
        array("/images/banner_rotation/shikaro89826.png", "/gallery/post/?search=vaporeon&feature=89826"),
        array("/images/banner_rotation/sefuart101447.png", "/gallery/post/?search=flareon&feature=101447"),
        array("/images/banner_rotation/watermelon87100.png", "/gallery/post/?search=flygon&feature=87100"),
        array("/images/banner_rotation/pienji101284.png", "/gallery/post/?search=goodra&feature=101284"),
    );

if (isset($_GET['image'])) {
    $index = rand(0, sizeof($mapping) - 1);
    $img_src = $domain_prefix.$mapping[$index][0];
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
