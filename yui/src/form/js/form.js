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

M.availability_examus.form.instId = 0;

M.availability_examus.form.getNode = function(json) {
    var html, node, root, id, modeId, durationId, schedulingId, keyId;

    /** Returns string from translations. */
    function getString(identifier) {
        return M.util.get_string(identifier, 'availability_examus');
    }

    M.availability_examus.form.instId += 1;

    id = 'examus' + M.availability_examus.form.instId;

    html = '<label> ' + getString('title') + ' </label><br>';

    durationId = id + '_duration';
    html += '<label for="' + durationId + '">' + getString('duration') + '</label> ';
    html += '<input type="text" name="duration" id="' + durationId + '">';

    schedulingId = id + '_scheduling';
    html += '<br><input type="checkbox" name="scheduling_required" id="' + schedulingId + '">';
    html += '<label for="' + schedulingId + '">' + getString('scheduling_required') + '</label> ';

    modeId = id + '_mode';
    html += '<br><label for="' + modeId + '">' + getString('mode') + '</label> ';
    html += '<select name="mode" id="' + modeId + '">';
    html += '  <option value="normal">' + getString('normal_mode') + '</option>';
    html += '  <option value="identification">' + getString('identification_mode') + '</option>';
    html += '  <option value="olympics">' + getString('olympics_mode') + '</option>';
    html += '</select>';

    html += '<div class="rules">';
    html += '<label>' + getString('rules') + '</label> ';
    for (var key in this.rules) {
        keyId = id + '_' + key;
        html += '  <br><input type="checkbox" name="' + key + '" id="' + keyId + '" value="' + key + '" >';
        html += '  <label for="' + keyId + '">' + getString(key) + '</label>';
    }
    html += '</div>';

    node = Y.Node.create('<span> ' + html + ' </span>');
    if (json.duration !== undefined) {
        node.one('input[name=duration]').set('value', json.duration);
    }

    if (json.mode !== undefined) {
        node.one('select[name=mode] option[value=' + json.mode + ']').set('selected', 'selected');
    }

    if (json.scheduling_required !== undefined) {
        if (json.scheduling_required) {
            node.one('input[name=scheduling_required]').set('checked', 'checked');
        }
    }

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
        root.delegate('click', function() {
            M.core_availability.form.update();
        }, '.availability_examus input[name=scheduling_required]');
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

    if (node.one('input[name=scheduling_required]').get('checked') === true) {
        value.scheduling_required = true;
    } else {
        value.scheduling_required = false;
    }

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