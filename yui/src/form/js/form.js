/* global M */
/**
 * JavaScript for form editing profile conditions.
 *
 * @module moodle-availability_examus-form
 */
/** @suppress checkVars */
M.availability_examus = M.availability_examus || {};

M.availability_examus.form = Y.Object(M.core_availability.plugin);

M.availability_examus.form.initInner = function() {
    // Nothing.
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

    node = Y.Node.create('<span> ' + html + ' </span>');
    if (json.duration !== undefined) {
        node.one('input[name=duration]').set('value', json.duration);
    }

    if (json.mode !== undefined) {
        node.one('select[name=mode] option[value=' + json.mode + ']').set('selected', 'selected');
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
    }

    return node;
};

M.availability_examus.form.fillValue = function(value, node) {
    value.duration = node.one('input[name=duration]').get('value').trim();
    value.mode = node.one('select[name=mode]').get('value').trim();
};

M.availability_examus.form.fillErrors = function(errors, node) {
    var value = {};
    this.fillValue(value, node);
    if (value.duration === undefined || !(new RegExp('^\\d+$')).test(value.duration) || value.duration % 30 !== 0) {
        errors.push('availability_examus:error_setduration');
    }
};