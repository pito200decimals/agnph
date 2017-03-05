{% extends "about/skin-base.tpl" %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/about/style.css') }}" />
    <style>
        .staff-list {
            list-style: none;
            margin: 0px;
            padding: 0px;
        }
        .staff-group-img {
            float: left;
            margin: 5px;
            margin-right: 15px;
        }
        .staff-list li {
            display: block;
        }
        .profile-img {
            max-width: 200px;
            max-height: 200px;
        }
        .profile-name {
            margin-top: 5px;
            display: inline-block;
            font-weight: bold;
        }
        .profile-bio {
        }
        .contact {
            font-size: 80%;
        }
        .contact span {
            font-weight: bold;
        }
    </style>
{% endblock %}

{% block content %}
    <h3>AGNPH Staff</h3>
    <div class="block">
        <div class="header">Contact</div>
        <div class="content">
            <p>
                If you have any questions, feel free to contact any of the staff members using the site's private message system. Or you can drop by AGNPH's <a href="/about/irc/"><strong>IRC</strong></a> and get real-time help.
            </p>
        </div>
    </div>
    <div class="block">
        <div class="header">Site Owner</div>
        <div class="content">
            <ul class="staff-list">
                <li>
                    <div class="staff-group-img">
                        <img class="profile-img" src="/images/staff/flygon.png" />
                    </div>
                    <a class="profile-name" href="/user/2/">Flygon</a>
                    <p class="profile-bio">
                        {#Sits in armchair, stares off into distance. Contemplates eating Fried Chicken.#}
                        Often sits in an armchair and stares off into the distance. Keep well fed with Fried Chicken.
                    </p>
                    <p class="contact">
                        <span>Contact via:</span> Site PM, IRC
                    </p>
                    <div class="Clear">&nbsp;</div>
                </li>
            </ul>
            <div class="Clear">&nbsp;</div>
        </div>
    </div>
    <div class="block">
        <div class="header">Site Administrators</div>
        <div class="content">
            <div class="staff-group-img">
                <img class="profile-img" src="/images/staff/hatch_cyn.png" />
            </div>
            <ul class="staff-list">
                <li>
                    <a class="profile-name" href="/user/43/">Cyn</a> <strong>(Fics)</strong>
                    <p class="profile-bio">
                        Friendly resident Cyndaquil. In charge of site software and Fics.
                    </p>
                    <p class="contact">
                        <span>Contact via:</span> IRC, Site PM
                    </p>
                </li>
                <li>
                    <a class="profile-name" href="/user/1/">HatchlingByHeart</a> <strong>(Minecraft)</strong>
                    <p class="profile-bio">
                        Chubby dragon. In charge of site tech and Minecraft.
                    </p>
                    <p class="contact">
                        <span>Contact via:</span> IRC, Site PM
                    </p>
                </li>
            </ul>
            <div class="Clear">&nbsp;</div>
        </div>
    </div>
    <div class="block">
        <div class="header">Section Admins and Moderators</div>
        <div class="content">
            <ul class="staff-list">
                <li>
                    <div class="staff-group-img">
                        <img class="profile-img" src="/images/staff/anonymless.png" />
                    </div>
                    <a class="profile-name" href="/user/204/">Anonymless</a> <strong>(Gallery)</strong>
                    <p class="profile-bio">
                        Cone connoisseur. Also known as Nymlus.
                    </p>
                    <p class="contact">
                        <span>Contact via:</span> Site PM
                    </p>
                    <div class="Clear">&nbsp;</div>
                </li>
                <li>
                    <div class="staff-group-img">
                        <img class="profile-img" src="/images/staff/smbcha.gif" />
                    </div>
                    <a class="profile-name" href="/user/8/">smbcha</a> <strong>(Gallery)</strong>
                    <p class="profile-bio">
                    </p>
                    <p class="contact">
                        <span>Contact via:</span> Site PM, IRC, <a href="http://www.furaffinity.net/user/sloshedmail">FurAffinity</a>
                    </p>
                    <div class="Clear">&nbsp;</div>
                </li>
                <li>
                    <div class="staff-group-img">
                        <img class="profile-img" src="/images/staff/alynna.png" />
                    </div>
                    <a class="profile-name" href="/user/93/">Alynna</a> <strong>(IRC)</strong>
                    <p class="profile-bio">
                        Classy, rainbow Sylveon.
                    </p>
                    <p class="contact">
                        <span>Contact via:</span> IRC
                    </p>
                    <div class="Clear">&nbsp;</div>
                </li>
                <li>
                    <div class="staff-group-img">
                        <img class="profile-img" src="/images/staff/kupok.gif" />
                    </div>
                    <a class="profile-name" href="/user/6298/">Kupok</a> <strong>(IRC)</strong>
                    <p class="profile-bio">
                        The oldest, Grumpiest IRC Troll of a Moogle on AGNPH.
                    </p>
                    <p class="contact">
                        <span>Contact via:</span> IRC
                    </p>
                    <div class="Clear">&nbsp;</div>
                </li>
                <li>
                    <div class="staff-group-img">
                        <img class="profile-img" src="/images/staff/anon.png" />
                    </div>
                    <a class="profile-name">leiger</a> <strong>(Minecraft)</strong>
                    <p class="profile-bio">
                    </p>
                    <p class="contact">
                        <span>Contact via:</span> IRC
                    </p>
                    <div class="Clear">&nbsp;</div>
                </li>
            </ul>
            <div class="Clear">&nbsp;</div>
        </div>
    </div>
{% endblock %}
