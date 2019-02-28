{% extends "about/skin-base.tpl" %}

{% block scripts %}
    {{ parent() }}
    {% if not user or user.AutoDetectTimezone %}
        <script src="{{ asset('timezone.js') }}"></script>
    {% endif %}
{% endblock %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/about/style.css')|raw }}
    <style>
        .content p:first-child {
            margin-top: 0px;
        }
        .content ul:last-child {
            margin-bottom: 0px;
        }
        #contributors-list {
            list-style: none;
            padding: 0px;
        }
        #contributors-list li {
            margin-top: 20px;
        }
        #contributors-list li p {
            margin-top: 5px;
            margin-bottom: 5px;
        }
    </style>
{% endblock %}

{% block content %}
    <h3>AGNPH Info</h3>
    <div class="block">
        <div class="header">About</div>
        <div class="content">
            <strong>AGNPH,</strong> (or <em>alt.games.nintendo.pokémon.hentai</em>), is an internet archive started around 1999 to house all manner of explicit pokémon content. <span class="warning">You must be 18 years or older to visit this site.</span>
        </div>
    </div>
    <div class="block">
        <div class="header">History</div>
        <div class="content">
            AGNPH has been around in some form or another since 1996. Originally just a newsgroup, it quickly expanded into a standalone site to house the group's growing archive in 1999. Since then, it's been through at least 4 versions of the site.
        </div>
    </div>
    <div class="block">
        <div class="header">Contributors</div>
        <div class="content">
            <p>
                This site wouldn't be where it is without its Community, and it's Contributors. It is not just <a href="/about/staff/">the Staff</a> that do all the work, after all!
            </p>
            <p>
                The following users, by no means an exhaustive list, have made important contributions to AGNPH in recent history.
            </p>
            <ul id="contributors-list">
                <li>
                    <strong>Claytail</strong> - <a href="http://www.furaffinity.net/user/claytail/">Fur Affinity</a><br />
                    <strong>Pienji</strong> - <a href="http://www.furaffinity.net/user/pienji/">Fur Affinity</a> / <a href="http://pienji.tumblr.com/">Tumblr</a><br />
                    <strong>Sefuart</strong> - <a href="http://www.sefuart.net/">Website</a><br />
                    <strong>Shikaro</strong> - <a href="http://www.furaffinity.net/user/shikaro/">Fur Affinity</a> / <a href="http://www.pixiv.net/member.php?id=1196214">Pixiv</a> / <a href="http://shikaro.tumblr.com/">Tumblr</a><br />
                    <strong>StreetDragon95</strong> - <a href="http://www.furaffinity.net/user/streetdragon95/">Fur Affinity</a> / <a href="http://streetdragon95.deviantart.com/">DeviantArt</a><br />
                    <strong>Watermelon</strong> - <a href="https://inkbunny.net/Watermelon/">Inkbunny</a><br />
                    <strong>ZwitterKitsune</strong> - <a href="http://www.furaffinity.net/user/zwitterkitsune/">Fur Affinity</a><br />
                    <p>
                    These artists have contributed toward AGNPH by allowing their artwork to be used to promote AGNPH.
                    </p>
                </li>
                <li>
                    <strong>Skye</strong> - <a href="http://www.furaffinity.net/user/skye.pyro/">Fur Affinity</a><br />
                    <p>
                    Skye contributed the fantastic vector artwork/font currently used by AGNPH.
                    </p>
                </li>
            </ul>
        </div>
    </div>
{% endblock %}
