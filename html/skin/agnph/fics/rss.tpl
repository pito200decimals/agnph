<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">

<channel>
  <title>AGNPH Fics</title>
  <link>http://agn.ph/fics/</link>
  <description>AGNPH Fics section</description>
  {% for story in stories %}
    <item>
        <title>
            {{ story.Title }} by {{ story.author }} - {{ story.last_chapter.Title }}
        </title>
        <link>
            http://agn.ph/fics/story/{{ story.StoryId }}/
        </link>
        <description>
            {% if story.last_chapter.ChapterNotes %}
                <div>
                    {{ story.last_chapter.ChapterNotes }}
                </div>
            {% endif %}
            <div>
                <p>
                    Updated {{ story.DateUpdated }}
                </p>
            </div>
        </description>
    </item>
  {% endfor %}
</channel>

</rss>