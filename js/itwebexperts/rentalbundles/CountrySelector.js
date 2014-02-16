var ITwebexperts_Rentalbundles_Country_Selector = Class.create({
    config: {
        requiredEntryClass: 'required-entry'
    },

    countrySelectors: null,
    arrivalDates: null,
    datepicker: null,
    datepickerCntr: null,

    initialize: function (config) {
        Object.extend(this.config, config);
        Event.observe(document, 'dom:loaded', this.onDomLoaded.bind(this));
    },

    onDomLoaded: function () {
        this.init();
    },

    onCountryChange: function (select, evt) {
        this[select.value ? 'onCountrySelect' : 'onCountryReset'](select);
    },

    onCountrySelect: function (select) {
        var nextSelect = this.getNextSelect(select),
            dateInput = this.getArrivalDateBySelect(select);

        this.addValidation(select);
        if (dateInput) {
            this.addValidation(dateInput);
        }

        if (nextSelect) {
            if (select.value && nextSelect.value) {
                this.onCountryReset(select);
            }

            this.hideSelectedOptions(nextSelect);
            var nextBlock = this.getBlockBySelect(nextSelect);

            if (nextBlock) {
                Element.show(nextBlock);
            }
        }
    },

    hideSelectedOptions: function (select) {
        var options = Element.select(select, 'option'),
            selected = this.getSelectedCountries();

        for (var i = 0; i < options.length; i++) {
            if (options[i].value) {
                options[i].disabled = -1 < selected.indexOf(options[i].value);
            }
        }
    },

    getSelectedCountries: function () {
        var filterValue = function (el) {
            return el.value
        };

        // Just selecting all countries and returning their non-empty values as array
        return this.countrySelectors.findAll(filterValue).collect(filterValue);
    },

    onCountryReset: function (currentSelect) {
        if (!this.testIfFirstSelect(currentSelect) && !currentSelect.value) {
            this.removeValidation(currentSelect);
        }
        while (currentSelect = this.getNextSelect(currentSelect)) {
            this.hideSelect(currentSelect);
        }
    },

    hideSelect: function (select) {
        this.removeValidation(select);
        var options = Element.select(select, 'option');
        for (var i = 0; i < options.length; i++) {
            options[i].disabled = false;

            if (!options[i].value) {
                options[i].selected = true;
            }
        }
        var block = this.getBlockBySelect(select);
        if (block) {
            Element.hide(block);
        }
    },

    removeValidation: function (select) {
        this._removeValidation(select);

        var dateInput = this.getArrivalDateBySelect(select);
        if (dateInput) {
            this._removeValidation(dateInput);
            if (dateInput.id && $jppr && $jppr.datepick && $jppr.datepick._clearDate) {
                $jppr.datepick._clearDate('#' + dateInput.id);
            }
        }
    },

    addValidation: function (el) {
        return this.toggleValidation(el, 'add');
    },

    _removeValidation: function (el) {
        return this.toggleValidation(el, 'remove');
    },

    toggleValidation: function (el, operation) {
        var funcName = 'add' == operation ? 'addClassName' : 'removeClassName';
        el[funcName](this.config.requiredEntryClass);
    },

    getNextBlock: function (block) {
        return Element.next(block, this.config.blockSelector);
    },

    getBlockBySelect: function (select) {
        return Element.up(select, this.config.blockSelector);
    },

    getSelectByBlock: function (block) {
        return Element.down(block, 'select');
    },

    getNextSelect: function (select) {
        var currentBlock = this.getBlockBySelect(select);
        if (currentBlock) {
            var nextBlock = this.getNextBlock(currentBlock);
            if (nextBlock) {
                return this.getSelectByBlock(nextBlock);
            }
        }
    },

    getArrivalDateBySelect: function (select) {
        var block = this.getBlockBySelect(select);
        if (block) {
            return Element.down(block, this.config.startDateSelector);
        }
    },

    testIfFirstSelect: function (select) {
        var block = this.getBlockBySelect(select);
        if (block) {
            return !Element.previous(block, this.config.blockSelector);
        }
    },

    showPicker: function(el, evt) {
        Element.show(this.datepickerCntr);
    },

    init: function () {
        this.countrySelectors = $$(this.config.countrySelectorClass);
        this.arrivalDates = $$(this.config.blockSelector + ' ' + this.config.startDateSelector);
        this.datepicker = $('countryDatePicker');
        this.datepickerCntr = $('countryDatePickerDiv');

        jQuery('#countryDatePicker').datepick({});

        this.arrivalDates.each(function(el) {
            Event.observe(el, 'click', this.showPicker.bind(this, el));
        }.bind(this))

        this.countrySelectors.each(function (el) {
            Event.observe(el, 'change', this.onCountryChange.bind(this, el));
        }.bind(this))
    }
});