{% block user_activity_block %}
    <div class="block">
        <div class="header">Users Online</div>
        <div class="content">
            <ul class="user-activity-stats">
                <li>Users Online: <a href="/user/list/">{{ user_activity.users_online }}</a></li>
                <li>Guests Online: {{ user_activity.guests_online }}</li>
                <li>Daily Users: {{ user_activity.users_today }}</li>
                <li>Visitors Today: {{ user_activity.unique_visits_today }}</li>
                {% if user_activity.newest_member %}
                    <li>Newest Member: <a href="/user/{{ user_activity.newest_member.UserId }}/">{{ user_activity.newest_member.DisplayName }}</a></li>
                {% endif %}
            </ul>
        </div>
    </div>
{% endblock %}
