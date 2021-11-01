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

    function formGroup(id, label, content) {
        return '<span class="availability-group form-group mb-2">' +
            '<div class="col-md-5 col-form-label d-flex pb-0 pr-md-0">' +
            '  <label for="' + id + '">' + label + '</label>' +
            '</div>' +
            '<div class="col-md-7 form-inline align-items-start felement">' +
            content +
            '</div>' +
            '</span>';
    }

    function setSchedulingState(){
        var manualmodes = ['normal', 'identification'];
        var mode = node.one('select[name=mode]').get('value').trim();
        var checked = manualmodes.indexOf(mode) >= 0;
        node.one('#' + schedulingRequiredId).set('checked', checked);
    }

    var html, node, value;

    M.availability_examus.form.instId += 1;

    var id = 'examus' + M.availability_examus.form.instId;
    var durationId = id + '_duration';
    var modeId = id + '_mode';
    var schedulingRequiredId = id + '_schedulingRequired';
    var autoReschedulingId = id + '_autoRescheduling';
    var isTrialId = id + '_isTrial';
    var identificationId = id + '_identification';
    var customRulesId = id + '_customRules';
    var noProtectionId = id + '_noProtection';
    var auxiliaryCameraId = id + '_auxCamera';

    var userAgreementId = id + 'userAgreement';

    html = '<label><strong>' + getString('title') + '</strong></label><br><br>';


    html += formGroup(durationId, getString('duration'),
        '<input type="text" name="duration" id="' + durationId + '" class="form-control">'
    );

    html += formGroup(modeId, getString('mode'),
        '<select name="mode" id="' + modeId + '" class="custom-select">' +
        '  <option value="normal">' + getString('normal_mode') + '</option>' +
        '  <option value="identification">' + getString('identification_mode') + '</option>' +
        '  <option value="olympics">' + getString('olympics_mode') + '</option>' +
        '  <option value="auto">' + getString('auto_mode') + '</option>' +
        '</select>'
    );

    html += formGroup(identificationId, getString('identification'),
        '<select name="identification" id="' + identificationId + '" class="custom-select">' +
        '  <option value="passport">' + getString('passport_identification') + '</option>' +
        '  <option value="face">' + getString('face_identification') + '</option>' +
        '  <option value="face_and_passport">' + getString('face_passport_identification') + '</option>' +
        '  <option value="skip">' + getString('skip_identification') + '</option>' +
        '</select>'
    );

    html += formGroup(schedulingRequiredId, getString('scheduling_required'),
        '<input type="checkbox" name="scheduling_required" id="' + schedulingRequiredId + '" value="1">&nbsp;' +
        '<label for="' + schedulingRequiredId + '">' + getString('enable') + '</label> '
    );

    html += formGroup(autoReschedulingId, getString('auto_rescheduling'),
        '<input type="checkbox" name="auto_rescheduling" id="' + autoReschedulingId + '" value="1">&nbsp;' +
        '<label for="' + autoReschedulingId + '">' + getString('enable') + '</label> '
    );

    html += formGroup(isTrialId, getString('is_trial'),
        '<input type="checkbox" name="istrial" id="' + isTrialId + '" value="1">&nbsp;' +
        '<label for="' + isTrialId + '">' + getString('enable') + '</label> '
    );

    html += formGroup(noProtectionId, getString('noprotection'),
        '<input type="checkbox" name="noprotection" id="' + noProtectionId + '" value="1">&nbsp;' +
        '<label for="' + noProtectionId + '">' + getString('enable') + '</label> '
    );

    html += formGroup(isTrialId, getString('auxiliary_camera'),
        '<input type="checkbox" name="auxiliarycamera" id="' + auxiliaryCameraId + '" value="1">&nbsp;' +
        '<label for="' + auxiliaryCameraId + '">' + getString('enable') + '</label> '
    );

    html += formGroup(userAgreementId, getString('user_agreement_url'),
        '<input name="useragreementurl" id="' + userAgreementId + '" class="form-control" value="" />'
    );

    html += formGroup(customRulesId, getString('custom_rules'),
        '<textarea name="customrules" id="' + customRulesId + '" style="width: 100%" class="form-control"></textarea>'
    );


    var ruleOptions = '';
    for (var key in this.rules) {
        var keyId = id + '_' + key;
        ruleOptions += '  <br><input type="checkbox" name="' + key + '" id="' + keyId + '" value="' + key + '" >';
        ruleOptions += '  <label for="' + keyId + '">' + getString(key) + '</label>';
    }

    html += formGroup(null, getString('rules'), '<div class="rules"' + ruleOptions + '</div>');


    if (this.groups) {

        var groupOptions = '';
        for (var i in this.groups) {
            id = this.groups[i].id;
            var name = this.groups[i].name;
            var checked = (json.groups instanceof Array && json.groups.indexOf(id) > -1) ? 'checked' : '';

            groupOptions += '<br>'
                + '<label>'
                + '<input value=' + id + ' type="checkbox" name=groups[] ' + checked + '>'
                + '&nbsp;' + name
                + '</label>';
        }

        html += formGroup(null, getString('select_groups'), '<div class="groups"' + groupOptions + '</div>');
    }


    if(json.creating){
        json.mode = 'normal';
        json.scheduling_required = true;
    }


    node = Y.Node.create('<span> ' + html + ' </span>');
    if (json.duration !== undefined) {
        node.one('input[name=duration]').set('value', json.duration);
    }

    if (json.mode !== undefined) {
        node.one('select[name=mode] option[value=' + json.mode + ']').set('selected', 'selected');
    }

    if (json.identification !== undefined) {
        node.one('select[name=identification] option[value=' + json.identification + ']').set('selected', 'selected');
    }

    if (json.auto_rescheduling !== undefined) {
        value = json.auto_rescheduling ? 'checked' : null;
        node.one('#' + autoReschedulingId).set('checked', value);
    }

    if (json.noprotection !== undefined) {
        value = json.noprotection ? 'checked' : null;
        node.one('#' + noProtectionId).set('checked', value);
    }

    if (json.istrial !== undefined) {
        value = json.istrial ? 'checked' : null;
        node.one('#' + isTrialId).set('checked', value);
    }


    if (json.auxiliarycamera !== undefined) {
        value = json.auxiliarycamera ? 'checked' : null;
        node.one('#' + auxiliaryCameraId).set('checked', value);
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

    if (json.customrules !== undefined) {
        node.one('#' + customRulesId).set('value', json.customrules);
    }

    if (json.useragreementurl !== undefined) {
        node.one('#' + userAgreementId).set('value', json.useragreementurl);
    }


    node.delegate('valuechange', function() {
        M.core_availability.form.update();
    }, 'input,textarea,select');

    node.delegate('click', function() {
        M.core_availability.form.update();
    }, 'input[type=checkbox]');

    node.delegate('valuechange', function() {
        setSchedulingState();
    }, '#'+modeId);

    //setSchedulingState();

    return node;
};

M.availability_examus.form.fillValue = function(value, node) {
    var rulesInputs, key;
    value.duration = node.one('input[name=duration]').get('value').trim();
    value.mode = node.one('select[name=mode]').get('value').trim();
    value.identification = node.one('select[name=identification]').get('value').trim();
    value.auto_rescheduling = node.one('input[name=auto_rescheduling]').get('checked');
    value.scheduling_required = node.one('input[name=scheduling_required]').get('checked');
    value.istrial = node.one('input[name=istrial]').get('checked');
    value.customrules = node.one('textarea[name=customrules]').get('value').trim();
    value.noprotection = node.one('input[name=noprotection]').get('checked');
    value.useragreementurl = node.one('input[name=useragreementurl]').get('value').trim();
    value.auxiliarycamera = node.one('input[name=auxiliarycamera]').get('checked');

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
