{% extends "about/base.tpl" %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/about/style.css') }}" />
{% endblock %}

{% block content %}
    <h3>Gallery Help Pages</h3>
    <div class="block">
        <div class="header">Do Not Post List</div>
        <div class="content">
            <p>
                Please read the entirety of the following policy before making a DNP request.
            </p>
            <p>
                As part of its function as an archive, AGNPH often displays content sourced from other websites, referred to as 'mirroring'.
                If you have a compelling reason to not want your art posted here, you can apply to be added to our Do Not Post list.
                Keep in mind, this will only stop your art from being posted HERE. If you don't want your artwork redistributed at all,
                to any site, we recommend putting a 'Do Not Distribute' label on each of your works.
            </p>
            <p>
                If you wish to be added to the DNP list, send a private message to <a href="/user/2/"><strong>Flygon</strong></a>. This can be via AGNPH, or you can use another site such as
                <a href="http://www.furaffinity.net/user/flygon">Fur Affinity</a> or <a href="https://inkbunny.net/flygon">InkBunny</a>. Please do not use email.
                If you do not receive a reply from Flygon within three working days, you can resend the request to any of the <a href="/about/staff/">gallery admins</a>.
                You will also be asked to provide proof of your identity as the artist. Usually, this will be for you to send a confirmatory Private Message from your artist
                account on a website where your art is posted.
            </p>
            <p>
                Please specify exactly what you want done. This can include having specific artwork removed, all artwork removed, and/or being added to the DNP. If the artwork was commissioned,
                permission for the removal must be gained from the ARTIST before making the removal request.
            </p>
            <p>
                Be polite and intelligible in your requests. We're not out to steal your art, and we perform this takedown service as a courtesy.
                If you scream, accuse or threaten, you can expect your request to be ignored.
            </p>
            <a href="/forums/thread/1600/">The current Do Not Post list can be viewed on the forums here.</a>
        </div>
    </div>
    <div class="block">
        <div class="header">Takedown Requests</div>
        <div class="content">
            <p>
                We will respect takedown requests from the <strong>Artist</strong> of the post in question. Owners of any characters must have the <strong>Artist</strong> request the post takedown.
                If the artwork is a commissioned piece, the commissioner may request a takedown <strong>only if</strong> either:
                <ol>
                    <li>Explicit permission is granted from the artist for the takedown of the posted image.</li>
                    <li>The artist does not have any publicly-viewable sources of the posted image.</li>
                </ol>
                In either case, the <strong>Artist</strong> has final say if the post should be taken down or restored.
            </p>
            <p>
                Contact <a href="/user/2/"><strong>Flygon</strong></a> or a Gallery Admin to request a takedown. 
            </p>
        </div>
    </div>
    <div class="block">
        <div class="header">How to Tag Posts</div>
        <div class="content">
            AGNPH has a robust community-dependent tagging system. As well as being able to search for specific artists or characters, you can search based on fetishes, species and genders (among other things). You can even order your search results based on such things as viewcount or picture dimensions.<br />
            However, we need your help as a community to keep pictures properly tagged. The following is a guide in learning to tag content properly, as well as uploading it. Remember that any normal member of the community is free to edit tags and upload new content to the site, so feel free to contribute!<br />
            Please note that we do NOT use the same tagging methods as e621! Please read this guide before contributing to the gallery!<br />
            <br />
            <b>Tagging Basics</b><br />
            Every picture has a series of tags, of various types. Some of these relate to the content of the picture, such as artist tags, character tags and general tags. Other tags, called metatags, describe information about the artwork. This includes the artwork's source, its size in pixels, and who uploaded the artwork in the first place.<br />
            To designate a tag as being as a specific type, there are specific codes you can use. For example, typing character: before a tag will define it as a character tag.<br />
            Note that you NEVER use spaces in tags! If you want to have a space in a tag, use an underscore! For example, character:Ash_Ketchum.<br />
            <br />
            <b>Tag Types</b><br />
            The following are the basic tag types used to define the content of a piece of artwork. Using these tag types appropriately will make tagging a lot easier.<br />
            <ul><li><b>Artist Tags:</b> The artist who drew the picture in question. There may be more than one artist tag if the artwork is a collaboration. To define an artist tag, type artist: before the tag.</li>
            <li><b>Character Tags:</b> Some Pokemon characters have specific names, particularly the human characters. In addition, some people come up with original Pokemon characters for themselves, with unique appearances. If the artwork contains named characters like this, you can define them by putting character: before the tag. Don't forget to use underscores instead of spaces! Note that most Pokemon are just named by their species, and have no name individually! In most cases, you'll therefore be using species tags instead of character tags.</li>
            <li><b>Species Tags:</b> Use this to define specific Pokemon species, such as Pikachu, Snivy or Garchomp! If there are human characters in the artwork, feel free to add 'human' as a species tag too. Use species: or spec: to define a species tag.</li>
            <li><b>General Tags:</b> Any other basic tags about what's in the artwork, such as genders or fetish content. There are a few other tags that go in this section too, such as the 'shiny' tag if there's an alternate-colour Pokemon present.</li>
            <li><b>Metatags:</b> These are special tags that define the file itself. Most of these are determined automatically, such as file size and the name of the uploader. However, there is one metatag, Source, that is very important and will be talked about later.</li></ul><br />
            <br />
            When you upload a file, you can also set the Content Rating and write a Description. You don't have to do either of these if you don't want to, especially as all artwork on AGNPH is meant to be at least Questionable anyway.<br />
            In case you were wondering, 'parenting' is an advanced option used to link a series of slightly-altered pictures. It won't be covered in this basic guide.<br />
            <br />
            <b>What Should You Tag?</b><br />
            The following information should be tagged on each picture when applicable:<br />
            <ul><li><b>Artist(s):</b> Who created the artwork? If the artist is known by more than one name, use their best-known alias. If you don't know who the artist is, use artist:unknown_artist as the artist tag.</li>
            <li><b>Characters(s):</b> Do any of the characters featured in the artwork have specific names? If so, tag each character's best-known name or their full name.</li>
            <li><b>Species:</b> What species are present, particularly Pokemon species? If there are humans in the artwork, feel free to add species:human as well.</li>
            <li><b>Genders:</b> Use general tags to tag each character's gender. 'Male' and 'female' are most common, as well as 'herm'. If you can't tell a character's gender for sure (either from the artwork or looking at the source page), use Ambiguous_Gender.</li>
            <li><b>Body Shape:</b> If a Pokemon has been anthropomorphised (given two legs and humanlike features where they would normally not have), add the anthro tag. Don't tag feral Pokemon as being so, since that's the default. You can also use taur for artwork containing a taur character.</li>
            <li><b>How Many?:</b> If there's only one character present, tag Solo. If there's three or four characters, you can optionally tag Threesome or Foursome. If there's more than four, tag Group (you can add 'orgy' too when appropriate, but that goes into the 'sex acts' section below).</li>
            <li><b>Sex Acts:</b> Briefly tag what the characters are doing. Common tags include sex (for vaginal penetration), anal_sex, handjob, fellatio and cunnilingus, frotting and tribadism, and orgy. Don't get too ridiculously specific with this. We don't want to end up with bucketloads of overly-specific, barely-used tags.</li>
            <li><b>Basic Positions:</b> If the characters are in a classic and well-known position, you can tag this. Such positions include missionary, 69, doggy_position and cowgirl_position.</li>
            <li><b>Sex Objects:</b> If a sex toy such as a dildo, vibrator, strap-on or or feeldoe is present, tag sex_toy. If a large bondage device is present such as a sawhorse or hoist, you can tag those too. As above, try to avoid getting too specific naming the toys. Try to predict what people will use as a search term.</li>
            <li><b>Fetish Content:</b> Tag the presence of significant fetishes. While there are many, some examples of fetishes to be tagged include foot_fetish, bondage, vore, inflation, latex, transformation, gore, watersports, scat, mind_control (of which hypnotism is a subfetish), micro and macro.</li></ul><br />
            Some fetishes have subfetishes, and these should be tagged when present. For example, vore subfetishes include cock_vore, anal_vore and unbirthing (among others). Again, try to avoid getting too ridiculously specific.<br />
            <br />
            <b>What Should You NOT Tag?</b><br />
            We do NOT use the 'Tag What You See' system used by e621. Don't go making tags like 'cock' or 'breast', or tagging every single tiny thing you can see in the picture like 'hair' or 'green'. The principle behind the AGNPH tagging system is for a concise, effective tagging system that tags things people are interested in searching for.<br />
            <b>Do NOT tag:</b><br />
            <ul><li>Facial expressions (winking, panting, blushing)</li>
            <li>Normal body parts (cock, nipple, anus, pubic_hair)</li>
            <li>Accessories (standard street clothing, piercings, tattoo, bracelet)</li>
            <li>Sexual fluids (semen, vaginal_fluids)</li>
            <li>Details of positioning (crossed_legs, raised_tail)</li>
            <li>Exotic sex positions (folded_deck_chair_position)</li>
            <li>Background location (bedroom, forest, etc)</li>
            <li>Background objects (television, bed)</li>
            <li>'Cub' (Too difficult to distinguish between a young Pokemon and a Pokemon that's simply unevolved)</li></ul><br />
            <br />
            <b>The 'Source' Metatag</b><br />
            As an archive, it's important that we list the sources of the artwork in our gallery. As a metatag, Source is entered into a field separate from the normal tags.<br />
            <br />
            <b>Here are some guidelines for adding a Source:</b><br />
            Link to the artwork's page, not to the image itself: Don't link to the url of the image, or to the gallery's front page. Link to the actual page on which the image can be found.<br />
            Linking to an artist's actual gallery is better than linking to another archive: Try to avoid linking to other archives such as e621, Paheal or WildCritters. Otherwise, people trying to find the artist's page can get forever trapped following source links from archive to archive. Link to the artist's specific gallery, such as on Fur Affinity, InkBunny, SoFurry or Pixiv.<br />
            If there's no known source, add the 'sourceme' tag: If an image has been tagged except for its source, and nobody knows the source, add a sourceme general tag. This can happen when the artwork was mirrored from another archive.<br />
            Use Google Image Search to find the source: Did you know that you can drag and drop images into Google Image Search? Just drop the image into the searchbar and it'll try to find where else that artwork can be found!<br />
            <br />
            <b>That's It!</b><br />
            If you've managed to sit through all that, congratulations, you now know all the basics of tagging using the AGNPH system! Don't be afraid to ask your peers via the forums whenever you're in doubt, as there will always be ambiguous cases that will make even the most experienced tagger hesitate. Have fun!
        </div>
    </div>
{% endblock %}
