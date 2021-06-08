YUI.add('moodle-availability_examus-form', function (Y, NAME) {

/**
 * JavaScript for form editing profile conditions.
 *
 * @module moodle-availability_examus-form
 */
/** @suppress checkVars */
M.availability_examus = M.availability_examus || {};

M.availability_examus.form = Y.Object(M.core_availability.plugin);

M.availability_examus.form.rules = null;

M.availability_examus.form.initInner = function(rules, groups) {
    this.rules = rules;
    this.groups = groups;
};

M.availability_examus.form.instId = 0;

M.availability_examus.form.getNode = function(json) {
    /**
     * @param {string} identifier A string identifier
     * @returns {string} A string from translations.
     */
    function getString(identifier) {
        return M.util.get_string(identifier, 'availability_examus');
    }

    var html, node, root, value;

    M.availability_examus.form.instId += 1;

    var id = 'examus' + M.availability_examus.form.instId;
    var durationId = id + '_duration';
    var modeId = id + '_mode';
    var schedulingRequiredId = id + '_schedulingRequired';
    var autoReschedulingId = id + '_autoRescheduling';

    html = '<label><strong>' + getString('title') + '</strong></label><br><br>';


    html += '<label for="' + durationId + '">' + getString('duration') + '</label> ';
    html += '<input type="text" name="duration" id="' + durationId + '">';

    html += '<br><label for="' + modeId + '">' + getString('mode') + '</label> ';
    html += '<select name="mode" id="' + modeId + '">';
    html += '  <option value="normal">' + getString('normal_mode') + '</option>';
    html += '  <option value="identification">' + getString('identification_mode') + '</option>';
    html += '  <option value="olympics">' + getString('olympics_mode') + '</option>';
    html += '</select>';

    html += '<br><label for="' +  schedulingRequiredId+ '">' + getString('scheduling_required') + '</label> ';
    html += '<input type="checkbox" name="scheduling_required" id="' + schedulingRequiredId + '" value="1">&nbsp;';
    html += '<label for="' + schedulingRequiredId + '">' + getString('enable') + '</label> ';

    html += '<br><label for="' + autoReschedulingId + '">' + getString('auto_rescheduling') + '</label> ';
    html += '<input type="checkbox" name="auto_rescheduling" id="' + autoReschedulingId + '" value="1">&nbsp;';
    html += '<label for="' + autoReschedulingId + '">' + getString('enable') + '</label> ';

    html += '<div class="rules" style="padding-bottom:20px">';
    html += '<label>' + getString('rules') + '</label> ';
    for (var key in this.rules) {
        var keyId = id + '_' + key;
        html += '  <br><input type="checkbox" name="' + key + '" id="' + keyId + '" value="' + key + '" >';
        html += '  <label for="' + keyId + '">' + getString(key) + '</label>';
    }
    html += '</div>';

    if (this.groups) {
        html += '<div class="groups">';

        html += '<label>' + getString('select_groups') + ':</label>';
        for (var i in this.groups) {
            id = this.groups[i].id;
            var name = this.groups[i].name;
            var checked = (json.groups instanceof Array && json.groups.indexOf(id) > -1) ? 'checked' : '';

            html += '<br>'
                + '<label>'
                + '<input value=' + id + ' type="checkbox" name=groups[] ' + checked + '>'
                + '&nbsp;' + name
                + '</label>';
        }

        html += '</div>';
    }

    node = Y.Node.create('<span> ' + html + ' </span>');
    if (json.duration !== undefined) {
        node.one('input[name=duration]').set('value', json.duration);
    }

    if (json.mode !== undefined) {
        node.one('select[name=mode] option[value=' + json.mode + ']').set('selected', 'selected');
    }

    if (json.auto_rescheduling !== undefined) {
        value = json.auto_rescheduling ? 'checked' : null;
        node.one('#' + autoReschedulingId).set('checked', value);
    }

    if (json.scheduling_required !== undefined) {
        value = json.scheduling_required ? 'checked' : null;
        node.one('#' + schedulingRequiredId).set('checked', value);
    }

    if (json.rules === undefined) {
        json.rules = this.rules;
    }

    for (var ruleKey in json.rules) {
        if (json.rules[ruleKey]) {
            node.one('.rules input[name=' + ruleKey + ']').set('checked', 'checked');
        }
    }

    if (!M.availability_examus.form.addedEvents) {
        M.availability_examus.form.addedEvents = true;
        root = Y.one(".availability-field");
        root.delegate('valuechange', function() {
            M.core_availability.form.update();
        }, '.availability_examus input[name=duration]');
        root.delegate('valuechange', function() {
            M.core_availability.form.update();
        }, '.availability_examus select[name=mode]');
        root.delegate('click', function() {
            M.core_availability.form.update();
        }, '.availability_examus .rules input');

        root.delegate('click', function() {
            M.core_availability.form.update();
        }, '.availability_examus input[type=checkbox]');

    }

    return node;
};

M.availability_examus.form.fillValue = function(value, node) {
    var rulesInputs, key;
    value.duration = node.one('input[name=duration]').get('value').trim();
    value.mode = node.one('select[name=mode]').get('value').trim();
    value.auto_rescheduling = node.one('input[name=auto_rescheduling]').get('checked');
    value.scheduling_required = node.one('input[name=scheduling_required]').get('checked');

    value.rules = {};
    rulesInputs = node.all('.rules input');
    Y.each(rulesInputs, function(ruleInput) {
        key = ruleInput.get('value');
        if (ruleInput.get('checked') === true) {
            value.rules[key] = true;
        } else {
            value.rules[key] = false;
        }
    });

    value.groups = [];
    rulesInputs = node.all('.groups input');
    Y.each(rulesInputs, function(ruleInput) {
        var id = ruleInput.get('value');
        if (ruleInput.get('checked') === true) {
            value.groups.push(id);
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


}, '@VERSION@', {"requires": ["base", "node", "event", "moodle-core_availability-form"]});
