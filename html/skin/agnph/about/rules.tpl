{% extends "about/base.tpl" %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/about/style.css') }}" />
    <style>
        #content li {
            margin: 10px;
        }
        #content ul li a {
            font-weight: bold;
        }
    </style>
{% endblock %}

{% block content %}
    <h3>AGNPH Rules</h3>
    <div class="block">
    <ul id="toc">
        <li><a href="#site">Site</a></li>
        <li><a href="#forums">Forums</a></li>
        <li><a href="#gallery">Gallery</a></li>
        <li><a href="#fics">Fics</a></li>
        {#<li><a href="#oekaki">Oekaki</a></li>#}
        <li><a href="#irc">IRC</a></li>
        <li><a href="#minecraft">Minecraft</a></li>
    </ul>
    </div>
    <div class="block">
        <a id="site"></a>
        <div class="header">Site Rules</div>
        <div class="content">
            <h4>Administration</h4>
            <ul>
                <li><span class="warning">You must be over 18 to use any section or service of this
                    site.</span> Seriously, even if you may feel you enjoy it here, this isn't a
                    conductive environment for a growing mind. Find somewhere else, and then come
                    back once you're 18.</li>
                <li>Everyone in the community, regardless of job or rank, is to be treated equally
                    in community matters, but admins have the absolute authority in technical or
                    site matters. Admins' decisions should never be defied, but at the same time,
                    the relationship between staff members and normal users should be one of mutual
                    respect. Be respectful to your staff, and you'll get respect back, and your
                    opinions and values will be taken into account.</li>
                <li>Deliberately pissing off the admins means you're deliberately accepting the
                    consequences of deliberately pissing off the admins. Don't do it.</li>
                <li>Don't backseat moderate. This means ordering users about, telling staff how to
                    do their job, and generally acting like a staff member when you're not.
                    Seriously, don't do it.</li>
                <li>Duplicate or puppet accounts won't be tolerated, all duplicate accounts will be
                    permanently banned and the main account will be severely penalized.</li>
                <li>Loophole abuse will not be tolerated. No creatively interpreting the rules.
                    If you're unsure about the implications of a rule, ask an admin for
                    clarification.</li>
                <li>Admins and Mods should never intentionally treat members disrespectfully under
                    normal terms. Users who break the rules may be an exception to this depending
                    on the situation and severity of the offence.</li>
                <li>Respect the chain of command. The decision of Administrators are absolute, so
                    please don't argue with them. The Admins have a responsibility to enforce all
                    rules without prejudice or bias.</li>
                <li>Similarly, Moderators are responsible for maintaining stability in the
                    community. Obey any request by them that relates to your behaviour, posts, or
                    other factors that affect the community in general. In other words, any
                    requests inside the Moderator's authority.</li>
                <li>Staff are NOT allowed to punish or penalize members based on a personal quarrel
                    or pet peeve. All penalization or punishment must be served professionally. No rule
                    broken, no penalty. Staff who violate this rule will face possible demotion and/or
                    suspension.</li>
                <li>No crying wolf. We have enough work to do already, don't come whining to us
                    with problems that are either fallacious or have obvious solutions.</li>
                <li>Repeat violations of any section's rules will result in your account being
                    restricted or banned.</li>
            </ul>
            <h4>Other Guidelines</h4>
            <ul>
                <li>Be polite and courteous, always. Trolling, outbursts or general asshattery will
                    potentially get you in a lot of trouble.</li>
                <li>Be sure to use proper English spelling and grammar.</li>
                <li>Have fun. This place isn't meant to be super serious. Don't be ridiculously
                    silly, though.</li>
            </ul>
        </div>
    </div>
    <div class="block">
        <a id="forums"></a>
        <div class="header">Forums Rules</div>
        <div class="content">
            <ul>
                <li>The Forums are NOT a chat room. We have an IRC channel for this at server
                    "agn.ph", on channel #agnph. If you create a forum thread, it must be
                    constructive/contributive to the community, or be of interest to at least a few
                    users.</li>
                <li>If a thread is removed or closed for any reason, do NOT recreate it. We will
                    NOT remove/close a thread that does not violate a rule, unless a good reason is
                    given by the creator. We may, however, move dead threads to an archive board.</li>
                <li>Do not make threads or posts that can be viewed as offensive towards race,
                    religion, gender, group, or individual. We may be a porn archive and have some
                    seriously dirty minds amongst our community, but that doesn't mean we will
                    tolerate any degree of bigotry.</li>
                <li>DO NOT talk about or post links to illegal material such as child pornography,
                    illegal software (warez), or the trade of illegal narcotics (drugs).</li>
                <li>Do not derail the thread. Try to keep the threads on topic. However, we know
                    and accept that it is normal for threads to venture off topic a bit.</li>
                <li>Avoid spamming or content-free posts. Posts that only contain an image or image
                    macros are included in this.</li>
                <li>Avoid double or triple posting. There is an edit button for a reason, use it.</li>
                <li>This website is one that's supposed to cater to a mature audience, so please
                    try to act mature yourself.</li>
            </ul>
        </div>
    </div>
    <div class="block">
        <a id="gallery"></a>
        <div class="header">Gallery Rules</div>
        <div class="content">
            <ul>
                <li>We do not accept content that is not Pokémon-themed.</li>
                <li>Uploaded pictures must be pornographic or sexually suggestive in nature. No
                    clean art allowed, unless it is part of a larger collection of explicit images
                    (such as a comic). Vore and other fetish material counts as pornographic and is
                    allowed, even without genitalia visible.</li>
                <li>We do not allow real-life pornographic content.</li>
                <li>Please do NOT attempt to somehow overcome any of AGNPH's filesize and
                    dimensions limits for any upload. There's a reason we set limits and they are
                    already pretty generous.</li>
                <li>Don't upload, without explicit permission from the artist(s), any art involving
                    artists listed on the <a href="/forums/thread/1600/">Do Not Post list</a>.</li>
                <li>Don't make creepy comments on artwork. It's good to show light-hearted
                    appreciation, but we don't need to know the sticky, disturbing details of what
                    you want to do to whatever character. Also, remember that artists often use
                    artwork characters to represent themselves. They might not appreciate hearing
                    about your theoretical intentions.</li>
                <li>Don't whine about pictures having content you don't like. You can blacklist
                    tags you don't like in your account settings.</li>
                <li>Follow the <a href="/about/gallery/">gallery tagging guidelines</a> when adding
                    tags.</li>
                <li>Don't vandalize tags. This includes adding stupid or deliberately wrong tags,
                    or removing good ones.</li>
                <li>Don't roleplay in the comments of gallery posts.</li>
                <li>Acceptable reasons for flagging artwork for deletion include inferior duplicates,
                    outdated versions of interactive works (such as Flash games), illegal content,
                    non-Pokémon content, and terrible quality uploads such as those with rampant
                    compression artifacts. 'Bad art', 'disgusting content' or 'I'm the artist and I
                    want this removed' are not valid reasons for flagging. If you want to be added
                    to the DNP list, see the relevant help page.</li>
            </ul>
        </div>
    </div>
    <div class="block">
        <a id="fics"></a>
        <div class="header">Fics Rules</div>
        <div class="content">
            <ul>
                <li>All submissions must be accompanied by a complete disclaimer. If a suitable
                    disclaimer is not included, the site administrators reserve the right to add a
                    disclaimer.
                    <p>(Sample Disclaimer): Disclaimer: All publicly recognizable characters,
                        settings, etc. are the property of their respective owners. The original
                        characters and plot are the property of the author. The author is in no way
                        associated with the owners, creators, or producers of any media franchise.
                        No copyright infringement is intended.</p></li>
                <li>Stories submitted must be related to or contain character(s) from or of the
                    world of Pokémon.</li>
                <li>Correct grammar and spelling are expected of all stories submitted to this
                    site. The site administrators reserve the right to request corrections in
                    submissions with a multitude of grammar and/or spelling errors. If such a
                    request is ignored, the story will be deleted.</li>
                <li>Proper formatting for all stories must be used. This include punctuation,
                    spacing, and above all paragraphing. Stories that are a simple block of text
                    are unacceptable as they are extremely difficult to read and make sense of. All
                    paragraphs must be separated by at least one line of blank space. Please
                    attempt to follow the basic conventions of writing when submitting a story to
                    our site (even if said story is submitted from a mobile device). The site
                    administrators reserve the right to request fixes for extremely ill-formatted
                    stories. If such a request is ignored, the story will be deleted.</li>
                <li>All stories should be submitted with appropriate ratings, tags and
                    <a href="/fics/tags/?search=type%3Awarning">warnings</a>. The site
                    administrators recognize that there is an audience for these stories, but
                    please respect those who do not wish to read them by labelling them
                    appropriately. If a story is submitted with incorrect or missing tags, admins
                    reserve the right to request these be fixed, or the story will be removed.</li>
                <li>Stories containing acts such as extreme violence, torture or vore must be
                    coupled with a warning to the reader. Basically, anything that there is
                    a warning tag for it, the story should be labelled as such.</li>
                <li>Titles or summaries such as "Please read", "Untitled", etc. are not acceptable
                    titles or summaries.</li>
                <li>All stories and chapters must be at least {{ fics_min_word_count }} words long.
                    Adding filler not related to the story to achieve this limit is prohibited. If
                    you have chapters shorter than {{ fics_min_word_count }} words, consider
                    consolidating multiple chapters into one.</li>
                <li>A number of authors have requested that fans refrain from writing fan-fiction
                    based on their work. Therefore, submittions will not be accepted based on the
                    works of another author unless you have obtained permission from the author
                    (be it fan-fiction or official literature).</li>
                <li>Stories with multiple chapters should be submitted as such and NEVER as
                    separate stories. Upload the first chapter of your story, then edit your story
                    to add additional chapters. If you have trouble with this, please contact one
                    of the site administrators or ask a friend to help you.</li>
                <li>Spoilers and advertisements in your submissions are strictly forbidden.</li>
                <li>Keep it civil, reviews and responses must be fair. Actions will be taken
                    against offenders (unfair flaming will be warned). If you want to talk to an
                    author or fan, leave a comment or send a private message. Conversations on
                    reviews are forbidden and will be removed.</li>
            </ul>
        </div>
    </div>
    {#<div class="block">
        <a id="oekaki"></a>
        <div class="header">Oekaki Rules</div>
        <div class="content">
            <strong>(TODO: Oekaki rules)</strong>
        </div>
    </div>#}
    <div class="block">
        <a id="irc"></a>
        <div class="header">IRC Rules</div>
        <div class="content">
            <ul>
                <li>NSFW links and links that auto-play sound should be marked as such.</li>
            </ul>
        </div>
    </div>
    <div class="block">
        <a id="minecraft"></a>
        <div class="header">Minecraft Rules</div>
        <div class="content">
            <ul>
                <li>Client mods that affect gameplay and give an unfair advantage to the player are
                    not allowed (flight, op, xray, etc). Mods like shaders, texture packs and
                    minimaps are allowed.</li>
                <li>Do not grief another player's area or items.</li>
            </ul>
        </div>
    </div>
{% endblock %}
