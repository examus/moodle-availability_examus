/* global M */
/**
 * JavaScript for form editing profile conditions.
 *
 * @module moodle-availability_examus-form
 */
/** @suppress checkVars */
M.availability_examus = M.availability_examus || {};

M.availability_examus.form = Y.Object(M.core_availability.plugin);

M.availability_examus.form.rules = null;

M.availability_examus.form.initInner = function(rules)
{
    this.rules = rules;
};

M.availability_examus.form.instId = 1;

M.availability_examus.form.getNode = function(json) {
    var html, node, root, id, modeId;

    /** Returns string from translations. */
    function getString(identifier) {
        return M.util.get_string(identifier, 'availability_examus');
    }

    id = 'examus' + M.availability_examus.form.instId;
    M.availability_examus.form.instId += 1;

    html = '<label> ' + getString('title') + ' </label><br>';

    html += '<label for"' + id + '">' + getString('duration') + '</label> ';
    html += '<input type="text" name="duration" id="' + id + '">';

    modeId = 'examus' + M.availability_examus.form.instId;
    html += '<br><label for"' + modeId + '">' + getString('mode') + '</label> ';
    html += '<select name="mode" id="' + modeId + '">';
    html += '  <option value="normal">' + getString('normal_mode') + '</option>';
    html += '  <option value="identification">' + getString('identification_mode') + '</option>';
    html += '  <option value="olympics">' + getString('olympics_mode') + '</option>';
    html += '</select>';

    //TODO: add scheduling_required

    html += '<div class="rules">';
    html += '<label>' + getString('rules') + '</label> ';
    for (var key in this.rules) {
        html += '  <br><input type="checkbox" name="' + key + '" id="' + key + '" value="' + key + '" >';
        html += '  <label for="' + key + '">' + getString(key) + '</label>';
    }
    html += '</div>';


    node = Y.Node.create('<span> ' + html + ' </span>');
    if (json.duration !== undefined) {
        node.one('input[name=duration]').set('value', json.duration);
    }

    if (json.mode !== undefined) {
        node.one('select[name=mode] option[value=' + json.mode + ']').set('selected', 'selected');
    }

    //TODO: add scheduling_required

    if (json.rules === undefined) {
        json.rules = this.rules
    }

    for (key in json.rules) {
        if (json.rules[key]) {
            node.one('.rules input[name=' + key + ']').set('checked', 'checked');
        }
    }

    if (!M.availability_examus.form.addedEvents) {
        M.availability_examus.form.addedEvents = true;
        root = Y.one(".availability-field");
        root.delegate('valuechange', function() {
            M.core_availability.form.update();
        }, '.availability_examus input[name=duration]');
        //TODO: add scheduling_required
        root.delegate('valuechange', function() {
            M.core_availability.form.update();
        }, '.availability_examus select[name=mode]');
        root.delegate('click', function() {
            M.core_availability.form.update();
        }, '.availability_examus .rules input');
    }

    return node;
};

M.availability_examus.form.fillValue = function(value, node) {
    var rulesInputs, key;
    value.duration = node.one('input[name=duration]').get('value').trim();
    value.mode = node.one('select[name=mode]').get('value').trim();
    //TODO: add scheduling_required

    value.rules = {};
    rulesInputs = node.all('.rules input');
    Y.each(rulesInputs, function (ruleInput) {
        key = ruleInput.get('value');
        if (ruleInput.get('checked') === true) {
            value.rules[key] = true;
        } else {
            value.rules[key] = false;
        }

    });
};

M.availability_examus.form.fillErrors = function(errors, node) {
    var value = {};
    this.fillValue(value, node);
    if (value.duration === undefined || !(new RegExp('^\\d+$')).test(value.duration) || value.duration % 30 !== 0) {
        errors.push('availability_examus:error_setduration');
    }
};