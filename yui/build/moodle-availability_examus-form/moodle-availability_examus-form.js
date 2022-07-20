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

M.availability_examus.form.initInner = function(rules, groups, warnings, scoring) {
    this.rules = rules;
    this.groups = groups;
    this.warnings = warnings;
    this.scoring = scoring;
};

M.availability_examus.form.instId = 0;

M.availability_examus.form.getNode = function(json) {
    /**
     * @param {string} identifier A string identifier
     * @returns {string} A string from translations.
     */
    function getString(identifier, module) {
        module = module || 'availability_examus';
        return M.util.get_string(identifier, module);
    }

    function moreLess(content){
        var showmore = getString('showmore', 'core_form');
        var showless = getString('showless', 'core_form');

        return '<a href="#" class="examus-moreless" data-more="' + showmore + '" data-less="' + showless + '">' +
            showmore +
            '</a><div class="hidden col-md-12">' + content + '</div>';
    }

    function switchMoreLessState(target){
      var next = target.next();
      var hidden = next.hasClass('hidden');

      if(hidden) {
          next.removeClass('hidden');
          target.setContent(target.getAttribute('data-less'));
      } else {
          next.addClass('hidden');
          target.setContent(target.getAttribute('data-more'));
      }
    }

    function formGroup(id, label, content, fullwidth) {
        var labelcols = fullwidth ? 10 : 5;
        var fieldcols = fullwidth ? 10 : 7;

        return '<span class="availability-group form-group mb-2">' +
            '<div class="col-md-'+labelcols+' col-form-label d-flex pb-0 pr-md-0">' +
            '  <label for="' + id + '">' + label + '</label>' +
            '</div>' +
            '<div class="col-md-'+fieldcols+' form-inline align-items-start felement">' +
            content +
            '</div>' +
            '</span>';
    }

    function setSchedulingState() {
        var manualmodes = ['normal', 'identification'];
        var mode = node.one('select[name=mode]').get('value').trim();
        var checked = manualmodes.indexOf(mode) >= 0;
        node.one('#' + schedulingRequiredId).set('checked', checked);
    }

    function nextTick(callback) {
        setTimeout(callback, 0);
    }

    function switchTab(tab){
        if(tab == 1) {
            tabButtonOne.addClass('btn-primary');
            tabButtonOne.removeClass('btn-secondary');
            tabButtonTwo.addClass('btn-secondary');
            tabButtonTwo.removeClass('btn-primary');
            tabOne.removeClass('hidden');
            tabTwo.addClass('hidden');
        } else {
            tabButtonTwo.addClass('btn-primary');
            tabButtonTwo.removeClass('btn-secondary');
            tabButtonOne.addClass('btn-secondary');
            tabButtonOne.removeClass('btn-primary');
            tabOne.addClass('hidden');
            tabTwo.removeClass('hidden');
        }
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

    html = formGroup(durationId, getString('duration'),
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
        ruleOptions += '<br><input type="checkbox" name="' + key + '" id="' + keyId + '" value="' + key + '" >&nbsp;';
        ruleOptions += '<label for="' + keyId + '" style="white-space: break-spaces">' + getString(key) + '</label>';
    }

    html += formGroup(null, getString('rules'), '<div class="rules" style="white-space:nowrap">' + ruleOptions + '</div>');


    if (this.groups) {
        var groupOptions = '';
        for (var i in this.groups) {
            var name = this.groups[i].name;
            var groups = (json.groups instanceof Array) ? json.groups : [];

            id = parseInt(this.groups[i].id);
            groups = groups.map(function(gid){ return parseInt(gid); });

            var checked = groups.indexOf(id) > -1 ? 'checked' : '';

            groupOptions += '<br>'
                + '<label>'
                + '<input value=' + id + ' type="checkbox" name=groups[] ' + checked + '>'
                + '&nbsp;' + name
                + '</label>';
        }

        html += formGroup(null, getString('select_groups'), '<div class="groups">' + groupOptions + '</div>');
    }


    var warningOptions = '';
    for (var wkey in this.warnings) {
        var wkeyId = id + '_' + wkey;
        warningOptions += '<input type="checkbox" name="' + wkey + '" id="' + wkeyId + '" value="' + wkey + '" >&nbsp;';
        warningOptions += '<label for="' + wkeyId + '" style="white-space: break-spaces">' + getString(wkey) + '</label><br>';
    }
    var scoringOptions = '';
    for (var skey in this.scoring) {
        var skeyId = id + '_' + skey;
        var smin = this.scoring[skey].min;
        var smax = this.scoring[skey].max;
        var scoringInputHTML = '<input type="number" class="examus-scoring-input" value=""' +
            'step="0.01" ' +
            'name="' + skey + '" ' +
            'id="scoring_' + skeyId + '" ' +
            'min="' + smin + '" max="' + smax + '">';

        scoringOptions += formGroup(skeyId, getString('scoring_'+skey), scoringInputHTML);
    }


    var htmlTwo = '';
    htmlTwo += formGroup(null, getString('visible_warnings'),
                 '<div class="warnings" style="white-space: nowrap" >' + moreLess(warningOptions) + '</div>',
                 true);

    htmlTwo += formGroup(null, getString('scoring_params_header'),
                 moreLess(scoringOptions),
                 true);



    node = Y.Node.create('<span class="availibility_examus-tabs" style="position:relative"></span>');

    node.setHTML('<label><strong>' + getString('title') + '</strong></label><br><br>');

    var tabButtons = Y.Node.create('<div style="position:absolute; top: 0; right: 0;" class="availibility_examus-tab-btns"></div>').appendTo(node);
    var tabButtonOne = Y.Node.create('<a href="#" class="btn btn-primary">1</a>').appendTo(tabButtons);
    var tabButtonTwo = Y.Node.create('<a href="#" class="btn btn-secondary">2</a>').appendTo(tabButtons);

    var tabOne = Y.Node.create('<div class="tab_content">' + html + '</div>').appendTo(node);
    var tabTwo = Y.Node.create('<div class="tab_content hidden">' + htmlTwo + '</div>').appendTo(node);


    if(json.creating){
        json.mode = 'normal';
        json.scheduling_required = true;
    }

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

    if (json.warnings === undefined) {
        json.warnings = this.warnings;
    } else {
        var warningRows = json.warnings;
        json.warnings = this.warnings;
        for(var wkey in warningRows) {
            json.warnings[wkey] = warningRows[wkey];
        }
    }

    for (var ruleKey in json.rules) {
        if (json.rules[ruleKey]) {
            var input = node.one('.rules input[name=' + ruleKey + ']');
            if(input) {
                input.set('checked', 'checked');
            }
        }
    }

    for (var warningKey in json.warnings) {
        if (json.warnings[warningKey]) {
            var winput = node.one('.warnings input[name=' + warningKey + ']');
            if(winput) {
                winput.set('checked', 'checked');
            }
        }
    }

    json.scoring = json.scoring || {};
    for (var scoringKey in json.scoring) {
        if (!isNaN(json.scoring[scoringKey])) {
            var sinput = node.one('.examus-scoring-input[name=' + scoringKey + ']');
            if(sinput) {
                sinput.set('value', json.scoring[scoringKey]);
            }
        }
    }


    if (json.customrules !== undefined) {
        node.one('#' + customRulesId).set('value', json.customrules);
    }

    if (json.useragreementurl !== undefined) {
        node.one('#' + userAgreementId).set('value', json.useragreementurl);
    }


    node.delegate('valuechange', function() {
        nextTick(function(){ M.core_availability.form.update(); });
    }, 'input,textarea,select');

    node.delegate('click', function() {
        nextTick(function(){ M.core_availability.form.update(); });
    }, 'input[type=checkbox]');

    node.delegate('valuechange', function() {
        setSchedulingState();
    }, '#'+modeId);

    tabButtonOne.on('click', function(e){
        e.preventDefault();
        switchTab(1);
    });
    tabButtonTwo.on('click', function(e){
        e.preventDefault();
        switchTab(2);
    });
    node.delegate('click', function(e) {
        e.preventDefault();
        switchMoreLessState(e.target);
    }, '.examus-moreless');


    //setSchedulingState();

    return node;
};

M.availability_examus.form.fillValue = function(value, node) {
    var rulesInputs, warningsInputs, scoringInputs, key;
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

    value.warnings = {};
    warningsInputs = node.all('.warnings input');
    Y.each(warningsInputs, function(warningInput) {
        key = warningInput.get('value');
        if (warningInput.get('checked') === true) {
            value.warnings[key] = true;
        } else {
            value.warnings[key] = false;
        }
    });

    value.scoring = {};
    scoringInputs = node.all('.examus-scoring-input');
    Y.each(scoringInputs, function(scoringInput) {
        key = scoringInput.get('name');
        var scoringValue = scoringInput.get('value').trim();
        if (scoringValue.length > 0) {
            value.scoring[key] = parseFloat(scoringValue);
        } else {
            value.scoring[key] = null;
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
