<!DOCTYPE html>
<html>
    <head>
        <title>My Webpage</title>
    </head>
    <body>
        <ul id="navigation">
        {% for item in navigation %}
            <li><a href="{{ item.href }}">{{ item.caption }}</a></li>
        {% endfor %}
        </ul>

        <h1>My Webpage</h1>
        {{ footer }}
        <a href="/include/auth/login.php">Login</a><br />
        <a href="/include/auth/logout.php">Log out</a>
    </body>
</html>
