{# ------------------------------------------------------ grid ------------------------------------------------------ #}
{% block grid %}
<div class="grid">
{% if grid.totalCount > 0 or grid.isFiltered or grid.noDataMessage is sameas(false) %}
    <form id="{{ grid.hash }}" action="{{ grid.routeUrl }}" method="post">
        <div class="grid_header">
        {% if grid.massActions|length > 0 %}
            {{ grid_actions(grid) }}
        {% endif %}
        </div>
        <div class="grid_body">
        <table>
        {% if grid.isTitleSectionVisible %}
            {{ grid_titles(grid) }}
        {% endif %}

        {% if grid.isFilterSectionVisible %}
            {{ grid_filters(grid) }}
        {% endif %}

        {{ grid_rows(grid) }}
        </table>
        </div>
        <div class="grid_footer">
        {% if grid.isPagerSectionVisible %}
            {{ grid_pager(grid) }}
        {% endif %}
        {% if grid.exports|length > 0 %}
            {{ grid_exports(grid) }}
        {% endif %}
        </div>
        {{ grid_scripts(grid) }}
    </form>
{% else %}
    {{ grid_no_data(grid) }}
{% endif %}
</div>
{% endblock grid %}
{# --------------------------------------------------- grid_no_data ------------------------------------------------- #}
{% block grid_no_data %}<p class="no_data">{{ grid.noDataMessage|default('No data')|trans|raw }}</p>{% endblock grid_no_data %}
{# --------------------------------------------------- grid_no_result ------------------------------------------------- #}
{% block grid_no_result %}
{% spaceless %}
{% set nbColumns = 0 %}
{% for column in grid.columns %}
    {% if column.visible(grid.isReadyForExport) %}
        {% set nbColumns = nbColumns + 1 %}
    {% endif %}
{% endfor %}
<tr class="grid-row-cells">
    <td class="last-column last-row" colspan="{{ nbColumns }}" style="text-align: center;">{{ grid.noResultMessage|default('No result')|trans|raw }}</td>
</tr>
{% endspaceless %}
{% endblock grid_no_result %}
{# --------------------------------------------------- grid_titles -------------------------------------------------- #}
{% block grid_titles %}
    <tr class="grid-row-titles">
    {% for column in grid.columns %}
        {% if column.visible(grid.isReadyForExport) %}
            <th class="{% if column.align != 'left'%}align-{{ column.align }}{% endif %}{% if loop.last %} last-column{% endif %}"{% if(column.size > -1) %} style="width:{{ column.size }}px;"{% endif %}>
            {% if column.type == 'massaction' %}
                <input type="checkbox" class="grid-mass-selector" onclick="{{ grid.hash }}_markVisible(this.checked);"/>
            {% else %}
                {% set columnTitle = grid.prefixTitle ~ column.title ~ '__abbr' %}
                {% if columnTitle|trans == columnTitle %}
                    {% set columnTitle = grid.prefixTitle ~ column.title %}
                {% endif %}
                {% if (column.sortable) %}
                    <a class="order" href="{{ grid_url('order', grid, column) }}" title="{{ 'Order by'|trans }} {{ columnTitle|trans }}">{{ columnTitle|trans }}</a>
                    {% if column.order == 'asc' %}
                        <div class="sort_up"></div>
                    {% elseif column.order == 'desc' %}
                        <div class="sort_down"></div>
                    {% endif %}
                {% else %}
                    {{ columnTitle|trans }}
                {% endif %}
            {% endif %}
            </th>
        {% endif %}
    {% endfor %}
    </tr>
{% endblock grid_titles %}
{# -------------------------------------------------- grid_filters -------------------------------------------------- #}
{% block grid_filters %}
    <tr class="grid-row-filters">
    {% for column in grid.columns %}
        {% if column.visible(grid.isReadyForExport) %}
        <th{% if loop.last %} class="last-column"{% endif %}{% if(column.size > -1) %} style="width:{{ column.size }}px;"{% endif %}>{% if column.filterable %}{{ grid_filter(column, grid)|raw }}{% endif %}</th>
        {% endif %}
    {% endfor %}
    </tr>
{% endblock grid_filters %}
{# -------------------------------------------------- grid_search -------------------------------------------------- #}
{% block grid_search %}
{% if grid.isFilterSectionVisible %}
    <div class="grid-search">
        <form id="{{ grid.hash }}_search" action="{{ grid.routeUrl }}" method="post">
        {% for column in grid.columns %}
            {% if column.visible(true) and column.isFilterable %}
                {% set columnTitle = grid.prefixTitle ~ column.title %}
                {% if column.filterable %}<div class="{{ cycle(['odd', 'even'], loop.index) }}"><label>{{ columnTitle|trans }}</label>{{ grid_filter(column, grid, false)|raw }}</div>{% endif %}
            {% endif %}
        {% endfor %}
            <div class="grid-search-action"><input type="submit" class="grid-search-submit" value="{{ 'Search'|trans }}"/><input type="button" class="grid-search-reset" value="{{ 'Reset'|trans }}" onclick="return {{ grid.hash }}_reset();"/></div>
        </form>
    </div>
{% endif %}
{% endblock grid_search %}
{# ---------------------------------------------------- grid_rows --------------------------------------------------- #}
{% block grid_rows %}
    {% for row in grid.rows %}
    {% set last_row = loop.last %}
    {% spaceless %}
        <tr{% if row.color != '' %} style="background-color:{{ row.color }};"{% endif %} class="grid-row-cells {{ cycle(['odd', 'even'], loop.index) }}">
        {% for column in grid.columns %}
            {% if column.visible(grid.isReadyForExport) %}
                <td class="grid-column-{{ column.id }}{% if column.align != 'left'%} align-{{ column.align }}{% endif %}{% if loop.last %} last-column{% endif %}{% if last_row %} last-row{% endif %}">{{ grid_cell(column, row, grid)|raw }}</td>
            {% endif %}
        {% endfor %}
    {% endspaceless %}
    </tr>
    {% else %}
        {{ grid_no_result(grid) }}
    {% endfor %}
{% endblock grid_rows %}
{# ---------------------------------------------------- grid_pager -------------------------------------------------- #}
{% block grid_pager %}
    {% if pagerfanta %}
        {{ grid_pagerfanta(grid) }}
    {% else %}
        <div class="pager" style="float:left">
            {{ grid_pager_totalcount(grid) }}
            {{ grid_pager_selectpage(grid) }}
            {{ grid_pager_results_perpage(grid) }}
        </div>
    {% endif %}
{% endblock grid_pager %}
{# ---------------------------------------------------- grid_pager_totalcount -------------------------------------------------- #}
{% block grid_pager_totalcount %}
{{ '%count% Results, ' | transchoice(grid.totalCount, {'%count%': grid.totalCount}) }}
{% endblock grid_pager_totalcount %}
{# ---------------------------------------------------- grid_pager_selectpage -------------------------------------------------- #}
{% block grid_pager_selectpage %}
{{ 'Page'|trans }}
{% spaceless %}
<input type="button" class="prev" {% if grid.page <= 0 %}disabled="disabled"{% endif %} value="<" onclick="return {{ grid.hash }}_previousPage();"/>
<input type="text" class="current" value="{{ grid.page + 1 }}" size="2" onkeypress="return {{ grid.hash }}_enterPage(event, parseInt(this.value)-1);"/>
<input type="button" value=">" class="next" {% if grid.page >= grid.pageCount-1 %}disabled="disabled"{% endif %} onclick="return {{ grid.hash }}_nextPage();"/> {{ 'of %count%'|trans({ '%count%' : grid.pageCount }) }}
{% endspaceless %}
{% endblock grid_pager_selectpage %}
{# ---------------------------------------------------- grid_pager_results_perpage -------------------------------------------------- #}
{% block grid_pager_results_perpage %}
{{ ', Display'|trans }}
<select onchange="return {{ grid.hash }}_resultsPerPage(this.value);">
{% for key, value in grid.limits %}
    <option value="{{ key }}"{% if (key == grid.limit) %} selected="selected"{% endif %}>{{ value }}</option>
{% endfor %}
</select> {{ 'Items per page'|trans }}
{% endblock grid_pager_results_perpage %}
{# --------------------------------------------------- grid_actions ------------------------------------------------- #}
{% block grid_actions %}
<div class="mass-actions">
    <span class="grid_massactions_helper">
        <a href="#" onclick="return {{ grid.hash }}_markVisible(true);">{{ 'Select visible'|trans }}</a> |
        <a href="#" onclick="return {{ grid.hash }}_markVisible(false);">{{ 'Deselect visible'|trans }}</a> |
        <a href="#" onclick="return {{ grid.hash }}_markAll(true);">{{ 'Select all'|trans }}</a> |
        <a href="#" onclick="return {{ grid.hash }}_markAll(false);">{{ 'Deselect all'|trans }}</a>
        <span class="mass-actions-selected" id="{{ grid.hash }}_mass_action_selected"></span>
    </span>
    {% spaceless %}
    <div style="float:right;" class="grid_massactions">
        {{ 'Action'|trans }}
        <input type="hidden" id="{{ grid.hash }}_mass_action_all" name="{{ grid.hash }}[{{ constant('APY\\DataGridBundle\\Grid\\Grid::REQUEST_QUERY_MASS_ACTION_ALL_KEYS_SELECTED') }}]" value="0"/>
        <select name="{{ grid.hash }}[{{ constant('APY\\DataGridBundle\\Grid\\Grid::REQUEST_QUERY_MASS_ACTION') }}]">
            <option value="-1"></option>
            {% for key, value in grid.massActions %}
            <option value="{{ key }}">{{ value.title|trans }}</option>
            {% endfor %}
        </select>
        <input type="submit"  value="{{ 'Submit Action'|trans }}"/>
    </div>
    {% endspaceless %}
</div>
{% endblock grid_actions %}
{# --------------------------------------------------- grid_exports ------------------------------------------------- #}
{% block grid_exports %}
<div class="exports" style="float:right">
    {% spaceless %}
        {{ 'Export'|trans }}
            <select name="{{grid.hash}}[{{ constant('APY\\DataGridBundle\\Grid\\Grid::REQUEST_QUERY_EXPORT') }}]">
            <option value="-1"></option>
            {% for key, value in grid.exports %}
            <option value="{{key}}">{{ value.title|trans }}</option>
            {% endfor %}
        </select>
        <input type="submit" value="{{ 'Export'|trans }}"/>
    {% endspaceless %}
</div>
{% endblock grid_exports %}
{# ------------------------------------------------ grid_column_actions_cell --------------------------------------------- #}
{% block grid_column_actions_cell %}
    {% for action in column.rowActions %}
        <a href="{{ url(action.route, column.routeParameters(row, action), false) }}" target="{{ action.target }}"{% if action.confirm %} onclick="return confirm('{{ action.confirmMessage }}')"{% endif %}{% for name, value in action.attributes %} {{ name }}="{{ value }}" {% endfor %}>{{ action.title|trans }}</a>{{ column.separator|raw }}
    {% endfor %}
{% endblock grid_column_actions_cell %}
{# ------------------------------------------------ grid_column_massaction_cell --------------------------------------------- #}
{% block grid_column_massaction_cell %}
    <input type="checkbox" class="action" value="1" name="{{ grid.hash }}[{{ column.id }}][{{ row.primaryFieldValue }}]"/>
{% endblock grid_column_massaction_cell %}
{# ------------------------------------------------ grid_column_boolean_cell --------------------------------------------- #}
{% block grid_column_boolean_cell %}
    {% set value = value is sameas(false) ? 'false' : value %}
    <span class="grid_boolean_{{ value }}" title="{{ value }}">{{ block('grid_column_cell') }}</span>
{% endblock grid_column_boolean_cell %}
{# ------------------------------------------------ grid_array_columns --------------------------------------------- #}
{% block grid_column_array_cell %}
{% set sourceValues = row.field(column.id) %}
{% set values = value %}
{% for key, index in values -%}
    {% set value = index %}
    {% set sourceValue = sourceValues[key] %}
    {{ block('grid_column_cell') | raw }}{{ column.separator | raw }}
{%- endfor %}
{% endblock grid_column_array_cell %}
{# ------------------------------------------- grid_column_cell ---------------------------------------- #}
{% block grid_column_cell %}
{% if column.filterable and column.searchOnClick %}
    {% set sourceValue = sourceValue is defined ? sourceValue : row.field(column.id) %}
    <a href="?{{ grid.hash }}[{{ column.id }}][from]={{ sourceValue | url_encode() }}">{{ value }}</a>
{% elseif column.safe is not null %}
    {% if column.safe =="raw" %}
        {{ value|raw }}
    {% else %}
        {{ value|escape(column.safe) }}
    {% endif %}
{% else %}
    {{ value }}
{% endif %}
{% endblock grid_column_cell %}
{# -------------------------------------------- grid_column_operator --------------------------------------- #}
{% block grid_column_operator %}
{% if column.operatorsVisible %}
<span class="grid-filter-operator">
    <select name="{{ grid.hash }}[{{ column.id }}][operator]" onchange="{{ grid.hash }}_switchOperator(this, '{{ grid.hash }}__{{ column.id }}__query__');">
    {% for operator in column.operators %}
        <option value="{{ operator }}"{% if op == operator %} selected="selected"{% endif %}>{{ operator |trans }}</option>
    {% endfor %}
    </select>
</span>
{% endif %}
{% endblock grid_column_operator %}
{# -------------------------------------------- grid_column_filter_type_input --------------------------------------- #}
{% block grid_column_filter_type_input %}
{% set btwOperator = constant('APY\\DataGridBundle\\Grid\\Column\\Column::OPERATOR_BTW') %}
{% set btweOperator = constant('APY\\DataGridBundle\\Grid\\Column\\Column::OPERATOR_BTWE') %}
{% set isNullOperator = constant('APY\\DataGridBundle\\Grid\\Column\\Column::OPERATOR_ISNULL') %}
{% set isNotNullOperator = constant('APY\\DataGridBundle\\Grid\\Column\\Column::OPERATOR_ISNOTNULL') %}
{% set op = column.data.operator is defined ? column.data.operator : column.defaultOperator %}
{% set from = column.data.from is defined ? column.data.from : null %}
{% set to = column.data.to is defined ? column.data.to : null %}
<span class="grid-filter-input">
    {{ block('grid_column_operator')}}
    <span class="grid-filter-input-query">
        <input type="{{ column.inputType }}" value="{{ from }}" class="grid-filter-input-query-from" name="{{ grid.hash }}[{{ column.id }}][from]" id="{{ grid.hash }}__{{ column.id }}__query__from" {% if submitOnChange is sameas(true) %}onkeypress="return {{ grid.hash }}_submitForm(event, this.form);"{% endif%} {{ ( op == isNullOperator or op == isNotNullOperator ) ? 'style="display: none;" disabled="disabled"' : '' }} />
        <input type="{{ column.inputType }}" value="{{ to }}" class="grid-filter-input-query-to" name="{{ grid.hash }}[{{ column.id }}][to]" id="{{ grid.hash }}__{{ column.id }}__query__to" {% if submitOnChange is sameas(true) %}onkeypress="return {{ grid.hash }}_submitForm(event, this.form);"{% endif%} {{ ( op == btwOperator or op == btweOperator ) ? '': 'style="display: none;" disabled="disabled"' }} />
    </span>
</span>
{% endblock grid_column_filter_type_input %}
{# -------------------------------------------- grid_column_filter_type_select --------------------------------------- #}
{% block grid_column_filter_type_select %}
{% set btwOperator = constant('APY\\DataGridBundle\\Grid\\Column\\Column::OPERATOR_BTW') %}
{% set btweOperator = constant('APY\\DataGridBundle\\Grid\\Column\\Column::OPERATOR_BTWE') %}
{% set isNullOperator = constant('APY\\DataGridBundle\\Grid\\Column\\Column::OPERATOR_ISNULL') %}
{% set isNotNullOperator = constant('APY\\DataGridBundle\\Grid\\Column\\Column::OPERATOR_ISNOTNULL') %}
{% set op = column.data.operator is defined ? column.data.operator : column.defaultOperator %}
{% set from = column.data.from is defined ? column.data.from : null %}
{% set to = column.data.to is defined ? column.data.to : null %}
{% set multiple = column.selectMulti %}
{% set expanded = column.selectExpanded %}
<span class="grid-filter-select">
    {{ block('grid_column_operator')}}
    <span class="grid-filter-select-query">
    {% if expanded %}
        <span class="grid-filter-select-query-from" id="{{ grid.hash }}__{{ column.id }}__query__from" {{ ( op == isNullOperator or op == isNotNullOperator ) ? 'style="display: none;" disabled="disabled"' : '' }}>
        {% for key, value in column.values %}
            <span><input type="{% if multiple %}checkbox{% else %}radio{% endif %}" name="{{ grid.hash }}[{{ column.id }}][from][]" value="{{ key }}" {% if key in from %} checked="checked"{% endif %} {% if submitOnChange is sameas(true) %}onclick="return {{ grid.hash }}_submitForm(event, this.form);"{% endif%}/><label>{{ value }}</label></span>
        {% endfor %}
        </span>
        <span class="grid-filter-select-query-to" id="{{ grid.hash }}__{{ column.id }}__query__to" {{ ( op == btwOperator or op == btweOperator ) ? '': 'style="display: none;" disabled="disabled"' }}>
        {% for key, value in column.values %}
            <span><input type="{% if multiple %}checkbox{% else %}radio{% endif %}" name="{{ grid.hash }}[{{ column.id }}][to]" value="{{ key }}" {% if not to is null and to == key %} checked="checked"{% endif %} {% if submitOnChange is sameas(true) %}onclick="return {{ grid.hash }}_submitForm(event, this.form);"{% endif%}/><label>{{ value }}</label></span>
        {% endfor %}
        </span>
        {% if multiple %}<input type="submit" value="{{ 'Go'|trans }}" />{% endif %}
    {% else %}
        <select{% if multiple %} multiple="multiple"{% endif %} name="{{ grid.hash }}[{{ column.id }}][from][]" class="grid-filter-select-query-from" id="{{ grid.hash }}__{{ column.id }}__query__from" {% if submitOnChange is sameas(true) %}onchange="return {{ grid.hash }}_submitForm(event, this.form);"{% endif%} {{ ( op == isNullOperator or op == isNotNullOperator ) ? 'style="display: none;" disabled="disabled"' : '' }}>
            <option value="">&nbsp;</option>
            {% for key, value in column.values %}
                <option value="{{ key }}"{% if key in from %} selected="selected"{% endif %}>{{ value }}</option>
            {% endfor %}
        </select>
        <select name="{{ grid.hash }}[{{ column.id }}][to]" class="grid-filter-select-query-to" id="{{ grid.hash }}__{{ column.id }}__query__to" {% if submitOnChange is sameas(true) %}onchange="return {{ grid.hash }}_submitForm(event, this.form);"{% endif%} {{ ( op == btwOperator or op == btweOperator ) ? '': 'style="display: none;" disabled="disabled"' }}>
            <option value="">&nbsp;</option>
            {% for key, value in column.values %}
                <option value="{{ key }}"{% if not to is null and to == key %} selected="selected"{% endif %}>{{ value }}</option>
            {% endfor %}
        </select>
        {% if multiple %}<input type="submit" value="{{ 'Go'|trans }}" />{% endif %}
    {% endif %}
    </span>
</span>
{% endblock grid_column_filter_type_select %}
{# -------------------------------------------- grid_column_filter_type_massaction --------------------------------------- #}
{% block grid_column_filter_type_massaction %}
    <input type="button" class="grid-search-reset" value="R" title="{{ 'Reset'|trans }}" onclick="return {{ grid.hash }}_reset();"/>
{% endblock grid_column_filter_type_massaction %}
{# -------------------------------------------- grid_column_filter_type_massaction_filter --------------------------------------- #}
{% block grid_column_filter_type_actions %}
    <a class="grid-reset" href="{{ grid_url('reset', grid) }}">{{ 'Reset'|trans }}</a>
{% endblock grid_column_filter_type_actions %}



{# --------------------------------------------------- grid_scripts -------------------------------------------------- #}
{% block grid_scripts %}
<script type="text/javascript">
{{ grid_scripts_goto(grid) }}
{{ grid_scripts_reset(grid) }}
{{ grid_scripts_previous_page(grid) }}
{{ grid_scripts_next_page(grid) }}
{{ grid_scripts_enter_page(grid) }}
{{ grid_scripts_results_per_page(grid) }}
{{ grid_scripts_mark_visible(grid) }}
{{ grid_scripts_mark_all(grid) }}
{{ grid_scripts_switch_operator(grid) }}
{{ grid_scripts_submit_form(grid) }}
{{ grid_scripts_ajax(grid) }}
</script>
{% endblock grid_scripts %}

{% block grid_scripts_goto %}
function {{ grid.hash }}_goto(url)
{
    window.location.href = url;

    return false;
}
{% endblock grid_scripts_goto %}

{% block grid_scripts_reset %}
function {{ grid.hash }}_reset()
{
    {{ grid.hash }}_goto('{{ grid_url('reset', grid) }}');

    return false;
}
{% endblock grid_scripts_reset %}

{% block grid_scripts_previous_page %}
function {{ grid.hash }}_previousPage()
{
    {{ grid.hash }}_goto('{{ grid_url('page', grid, grid.page - 1) }}');

    return false;
}
{% endblock grid_scripts_previous_page %}

{% block grid_scripts_next_page %}
function {{ grid.hash }}_nextPage()
{
    {{ grid.hash }}_goto('{{ grid_url('page', grid, grid.page + 1) }}');

    return false;
}
{% endblock grid_scripts_next_page %}

{% block grid_scripts_enter_page %}
function {{ grid.hash }}_enterPage(event, page)
{
    key = event.which;

    if (window.event) {
        key = window.event.keyCode; //IE
    }

    if (key == 13) {
        {{ grid.hash }}_goto('{{ grid_url('page', grid) }}' + page);

        return false;
    }
}
{% endblock grid_scripts_enter_page %}

{% block grid_scripts_results_per_page %}
function {{ grid.hash }}_resultsPerPage(limit)
{
    {{ grid.hash }}_goto('{{ grid_url('limit', grid) }}' + limit);

    return true;
}
{% endblock grid_scripts_results_per_page %}

{% block grid_scripts_mark_visible %}
function {{ grid.hash }}_markVisible(select)
{
    var form = document.getElementById('{{ grid.hash }}');

    var counter = 0;

    for (var i=0; i < form.elements.length; i++ ) {
        if (form.elements[i].type == 'checkbox') {
            form.elements[i].checked = select;

            if (form.elements[i].checked){
               counter++;
            }
        }
    }

    {% if grid.isFilterSectionVisible %}
    counter--;
    {% endif %}

    var selected = document.getElementById('{{ grid.hash }}_mass_action_selected');
    selected.innerHTML = counter > 0 ? '{{ 'Selected _s_ rows'|trans }}'.replace('_s_', counter) : '';

    document.getElementById('{{ grid.hash }}_mass_action_all').value = '0';

    return false;
}
{% endblock grid_scripts_mark_visible %}

{% block grid_scripts_mark_all %}
function {{ grid.hash }}_markAll(select)
{
    var form = document.getElementById('{{ grid.hash }}');

    for (var i=0; i < form.elements.length; i++ ) {
        if (form.elements[i].type == 'checkbox') {
            form.elements[i].checked = select;
        }
    }

    var selected = document.getElementById('{{ grid.hash }}_mass_action_selected');

    if (select) {
        document.getElementById('{{ grid.hash }}_mass_action_all').value = '1';
        selected.innerHTML = '{{ 'Selected _s_ rows'|trans }}'.replace('_s_', '{{ grid.totalCount }}');
    } else {
        document.getElementById('{{ grid.hash }}_mass_action_all').value = '0';
        selected.innerHTML = '';
    }

    return false;
}
{% endblock grid_scripts_mark_all %}

{% block grid_scripts_switch_operator %}
{% set btwOperator = constant('APY\\DataGridBundle\\Grid\\Column\\Column::OPERATOR_BTW') %}
{% set btweOperator = constant('APY\\DataGridBundle\\Grid\\Column\\Column::OPERATOR_BTWE') %}
{% set isNullOperator = constant('APY\\DataGridBundle\\Grid\\Column\\Column::OPERATOR_ISNULL') %}
{% set isNotNullOperator = constant('APY\\DataGridBundle\\Grid\\Column\\Column::OPERATOR_ISNOTNULL') %}
function {{ grid.hash }}_switchOperator(elt, query_)
{
    var inputFrom = document.getElementById(query_+'from');
    var inputTo = document.getElementById(query_+'to');
    if ((elt.options[elt.selectedIndex].value == '{{ btwOperator }}') || (elt.options[elt.selectedIndex].value == '{{ btweOperator }}')) {
        inputFrom.style.display = '';
        inputFrom.disabled=false;
        inputTo.style.display = '';
        inputTo.disabled=false;
    } else if ((elt.options[elt.selectedIndex].value == '{{ isNullOperator }}') || (elt.options[elt.selectedIndex].value == '{{ isNotNullOperator }}')) {
        inputFrom.style.display = 'none';
        inputFrom.disabled=true;
        inputTo.style.display = 'none';
        inputTo.disabled=true;
        elt.form.submit();
    } else {
        inputFrom.style.display = '';
        inputFrom.disabled=false;
        inputTo.style.display = 'none';
        inputTo.disabled=true;
    }
}
{% endblock grid_scripts_switch_operator %}

{% block grid_scripts_submit_form %}
function {{ grid.hash }}_submitForm(event, form)
{
    key = event.which;

    if (window.event) {
        key = window.event.keyCode; //IE
    }

    if (event.type != 'keypress' || key == 13) {
        form.submit();
    }

    return true;
}
{% endblock grid_scripts_submit_form %}

{% block grid_scripts_ajax %}
{% endblock grid_scripts_ajax %}
