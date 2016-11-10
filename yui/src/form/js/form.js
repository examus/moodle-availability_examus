/*global M*/
/**
 * JavaScript for form editing profile conditions.
 *
 * @module moodle-availability_examus-form
 */
M.availability_examus = M.availability_examus || {};

M.availability_examus.form = Y.Object(M.core_availability.plugin);

M.availability_examus.form.initInner = function() {
    // Nothing
};

M.availability_examus.form.instId = 1;

M.availability_examus.form.getNode = function(json) {
    var html, node, root, id, strings;
    strings = M.str.availability_examus;

    id = 'examus' + M.availability_examus.form.instId;
    M.availability_examus.form.instId += 1;

    html = '<label> ' + strings.title + ' </label><br>';
    html += '<label for"' + id + '">' + strings.duration + '</label>';
    html += '<input type="text" name="duration" id="' + id + '">';
    node = Y.Node.create('<span> ' + html + ' </span>');
    if (json.duration !== undefined) {
        node.one('input[name=duration]').set('value', json.duration);
    }

    if (!M.availability_examus.form.addedEvents) {
        M.availability_examus.form.addedEvents = true;
        root = Y.one('#fitem_id_availabilityconditionsjson');
        root.delegate('valuechange', function () {
            // Trigger the updating of the hidden availability data whenever the password field changes.
            M.core_availability.form.update();
        }, '.availability_examus input[name=duration]');
    }

    return node;
};

M.availability_examus.form.fillValue = function(value, node) {
    value.duration = node.one('input[name=duration').get('value').trim();
};

M.availability_examus.form.fillErrors = function(errors, node) {
    var value = {};
    this.fillValue(value, node);

    if (value.duration === undefined || !/^\d+$/.test(value.duration)) {
        errors.push('availability_examus:error_setduration');
    }
};