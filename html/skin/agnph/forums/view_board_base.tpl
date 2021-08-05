{% extends 'forums/skin-base.tpl' %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/list-style.css')|raw }}
    {{ inline_css_asset('/forums/board-style.css')|raw }}
{% endblock %}

{% block scripts %}
    {{ parent() }}
    {% if canAdminBoard %}
        <script>
            function PromptForNameAndDescriptionAndSubmit(id) {
                var form = $("#"+id);
                var nameField = form.find("[name='name']");
                var descriptionField = form.find("[name='description']");
                var name = window.prompt("Board name:", "");
                if (name === null || name === "") {
                    return false;
                }
                nameField.val(name);
                var description = prompt("Board description (blank to leave unchanged):", "");
                if (description === null) {
                    return false;
                }
                if (description !== "") {
                    descriptionField.val(description);
                } else {
                    descriptionField.val("");
                }
                form.submit();
                return false;
            }
            function PromptForConfirmAndSubmit(id) {
                if (confirm("Are you sure you want to delete this board?")) {
                    var form = $("#"+id);
                    form.submit();
                }
                return false;
            }
        </script>
    {% endif %}
{% endblock %}

{% block actionbar %}
    {% if user %}
        <ul class="forums-actionbar">
            {% if board.childBoards|length > 0 %}
                {# Allow mass-marking-as-read when not a leaf board #}
                {% if board.BoardId == -1 %}
                    <li><a href="/forums/mark-all-read/">Mark all as read</a></li>
                {% else %}
                    <li><a href="/forums/mark-all-read/?board={{ board.BoardId }}">Mark all as Read</a></li>
                {% endif %}
                {% if canAdminBoard %}
                    <br />
                {% endif %}
            {% endif %}
            {% if canAdminBoard %}

                {# Allow locking board #}
                {% if board.BoardId != -1 %}
                    {% if board.Locked %}
                        <form id="unlock-board-form" method="POST" accept-encoding="UTF-8" hidden>
                            <input type="hidden" name="action" value="unlock" />
                        </form>
                        <li><a href="/forums/unlock-board/" onclick="document.getElementById('unlock-board-form').submit();return false;">Unlock Board</a></li>
                    {% else %}
                        <form id="lock-board-form" method="POST" accept-encoding="UTF-8" hidden>
                            <input type="hidden" name="action" value="lock" />
                        </form>
                        <li><a href="/forums/lock-board/" onclick="document.getElementById('lock-board-form').submit();return false;">Lock Board</a></li>
                    {% endif %}

                    {# Allow marking board as private #}
                    {% if board.PrivateBoard %}
                        <form id="public-board-form" method="POST" accept-encoding="UTF-8" hidden>
                            <input type="hidden" name="action" value="mark-public" />
                        </form>
                        <li><a href="/forums/public-board/" onclick="document.getElementById('public-board-form').submit();return false;">Mark Public</a></li>
                    {% else %}
                        <form id="private-board-form" method="POST" accept-encoding="UTF-8" hidden>
                            <input type="hidden" name="action" value="mark-private" />
                        </form>
                        <li><a href="/forums/private-board/" onclick="document.getElementById('private-board-form').submit();return false;">Mark Private</a></li>
                    {% endif %}
                <br />
                {% endif %}

                {# Delete this board #}
                {% if board.childBoards|length == 0 and threads|length == 0 %}
                    <form id="delete-board-form" method="POST" accept-encoding="UTF-8" hidden>
                        <input type="hidden" name="action" value="delete" />
                    </form>
                    <li><a href="/forums/delete-board/" onclick="return PromptForConfirmAndSubmit('delete-board-form');">Delete Board</a></li>
                {% endif %}

                {# Create new child board #}
                <form id="create-board-form" method="POST" accept-encoding="UTF-8" hidden>
                    <input type="hidden" name="action" value="create" />
                    <input type="hidden" name="name" value="" />
                    <input type="hidden" name="description" value="" />
                </form>
                <li><a href="/forums/create-board/" onclick="return PromptForNameAndDescriptionAndSubmit('create-board-form');">Create Child Board</a></li>
                <br />

                {# Allow renaming board #}
                {% if board.BoardId != -1 %}
                    <form id="rename-board-form" method="POST" accept-encoding="UTF-8" hidden>
                        <input type="hidden" name="action" value="rename" />
                        <input type="hidden" name="name" value="" />
                        <input type="hidden" name="description" value="" />
                    </form>
                    <li><a href="/forums/rename-board/" onclick="return PromptForNameAndDescriptionAndSubmit('rename-board-form');">Rename Board</a></li>
                {% endif %}

                {# Allow moving board #}
                {% if board.BoardId != -1 %}
                    <li>
                        Move Board:
                        <form id="move-board-form" style="display: inline-block;" method="POST" accept-encoding="UTF-8">
                            <input type="hidden" name="action" value="move-board" />
                            <select name="parent-board" onchange="document.getElementById('move-board-form').submit();return false;">
                                {% for boardOption in allBoards %}
                                    <option value="{{ boardOption.BoardId }}"{% if boardOption.BoardId == board.ParentId %}{{ " " }}selected{% endif %}>{% if boardOption.depth > 0 %}{% for depth in 1..boardOption.depth %}&nbsp;&nbsp;{% endfor %}{% endif %}{{ boardOption.Name }}</option>
                                {% endfor %}
                            </select>
                        </form>
                    </li>
                {% endif %}
            {% endif %}
        </ul>
        <div class="Clear">&nbsp;</div>
    {% endif %}
{% endblock %}

{% block content %}
{% endblock %}
