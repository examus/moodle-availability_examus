YUI.add('moodle-availability_examus-form', function (Y, NAME) {

/*global M*/
/**
 * JavaScript for form editing profile conditions.
 *
 * @module moodle-availability_examus-form
 */
M.availability_examus = M.availability_examus || {};

M.availability_examus.form = Y.Object(M.core_availability.plugin);

// M.availability_examus.form.initInner = function(param) {
//     // The 'param' variable is the parameter passed through from PHP (you
//     // can have more than one if required).
//
//     // Using the PHP code above it'll show 'The param was: frog'.
//     console.log('The param was: ' + param);
// };

M.availability_examus.form.getNode = function(json) {
    var strings = M.str.availability_examus;
    var html = '<label> ' + strings.title + ' </label>';
    var node = Y.Node.create('<span> ' + html + ' </span>');

    return node;
};

M.availability_examus.form.fillValue = function(value, node) {
};

M.availability_examus.form.fillErrors = function(errors, node) {
};

}, '@VERSION@', {"requires": ["base", "node", "event", "moodle-core_availability-form"]});
