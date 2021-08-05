<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">

<channel>
  <title>AGNPH Fics</title>
  <link>http://agn.ph/fics/</link>
  <description>AGNPH Fics section</description>
  <lastBuildDate>{{ lastUpdateDate }}</lastBuildDate>
  {% for story in stories %}
    <item>
        <title>
            {{ story.Title }} by{{ " " }}{{ story.author.DisplayName }} - {{ story.last_chapter.Title }}
        </title>
        <link>
            http://agn.ph/fics/story/{{ story.StoryId }}/{{ story.lastChapterNum }}/
        </link>
        <description>
            {% if story.last_chapter.ChapterNotes %}
                <div>
                    {% autoescape false %}
                        {{ story.last_chapter.ChapterNotes }}
                    {% endautoescape %}
                </div>
            {% endif %}
            <div>
                <p>
                    Updated{{ " " }}{{ story.DateUpdated }}
                </p>
            </div>
        </description>
        <pubDate>
            {{ story.pubDate }}
        </pubDate>
        <guid>
            {{ story.updateGuid }}
        </guid>
    </item>
  {% endfor %}
</channel>

</rss>