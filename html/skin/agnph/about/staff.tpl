{% extends "about/skin-base.tpl" %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/about/style.css')|raw }}
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
                If you have any questions, feel free to contact any of the staff members using the site's private message system. Or you can drop by AGNPH's <a href="/about/irc/"><strong>IRC</strong></a> or <a href="/about/irc/"><strong>Discord</strong></a> and get real-time help.
            </p>
        </div>
    </div>
    <div class="block">
        <div class="header">Site Administrators</div>
        <div class="content">
            <ul class="staff-list">
                <li>
                    <div class="staff-group-img">
                        <img class="profile-img" src="/images/staff/keiro.png" />
                    </div>
                    <a class="profile-name" href="/user/10855/">Keiro</a> <strong>(Head Admin)</strong>
                    <p class="profile-bio">
                        Derptastic Arcanine; headpats are well-received.
                    </p>
                    <p class="contact">
                        <span>Contact via:</span> <a href="mailto:admin@heimkoma.com">Email</a>, Discord
                    </p>
                    <div class="Clear">&nbsp;</div>
                </li>
                <li>
                    <div class="staff-group-img">
                        <img class="profile-img" src="/images/staff/cyn.png" />
                    </div>
                    <a class="profile-name" href="/user/43/">Cyn</a> <strong>(Fics)</strong>
                    <p class="profile-bio">
                        Friendly resident Cyndaquil. In charge of site software and Fics.
                    </p>
                    <p class="contact">
                        <span>Contact via:</span> Discord/IRC, Site PM
                    </p>
                    <div class="Clear">&nbsp;</div>
                </li>
                <li>
                    <div class="staff-group-img">
                        <img class="profile-img" src="/images/staff/hatch.png" />
                    </div>
                    <a class="profile-name" href="/user/1/">HatchlingByHeart</a>
                    <p class="profile-bio">
                        Chubby Dragonite. Server consultant.
                    </p>
                    <p class="contact">
                        <span>Contact via:</span> Discord/IRC, Site PM
                    </p>
                    <div class="Clear">&nbsp;</div>
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
                        <span>Contact via:</span> <a href="https://www.furaffinity.net/user/anonymless">FurAffinity</a>
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
                        <span>Contact via:</span> Site PM, Discord/IRC, <a href="http://www.furaffinity.net/user/sloshedmail">FurAffinity</a>
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
                        <span>Contact via:</span> Discord/IRC
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
                        <span>Contact via:</span> Discord/IRC
                    </p>
                    <div class="Clear">&nbsp;</div>
                </li>
            </ul>
            <div class="Clear">&nbsp;</div>
        </div>
    </div>
{% endblock %}
