var ITwebexperts_Rentalbundles_Country_Selector = Class.create({
    config: {},

    countrySelectors: null,

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
        var nextSelect = this.getNextSelect(select);
        if (nextSelect) {
            if (nextSelect.value) {
                this.onCountryReset(select, true);
                return;
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
            if (options[i].value && (-1 < selected.indexOf(options[i].value))) {
                options[i].disabled = true;
            }
            else {
                options[i].disabled = false;
            }
        }
    },

    getSelectedCountries: function () {
        // Just selecting all countries and returning their non-empty values as array
        return this.countrySelectors
            .findAll(function (el) {
                return el.value
            })
            .collect(function (el) {
                return el.value
            });
    },

    onCountryReset: function (currentSelect, noHide) {
        while (currentSelect = this.getNextSelect(currentSelect)) {
            this.hideSelect(currentSelect, noHide);
        }
    },

    hideSelect: function (select, noHide) {
        var options = Element.select(select, 'option');

        for (var i = 0; i < options.length; i++) {
            options[i].disabled = false;

            if (!options[i].value) {
                options[i].selected = true;
            }
        }

        if (!noHide) {
            var block = this.getBlockBySelect(select);
            if (block) {
                Element.hide(block);
            }
        }
    },

    getNextBlock: function (block) {
        return Element.next(block, this.config.blockSelector);
    },

    getBlockBySelect: function (select) {
        return Element.up(select, this.config.blockSelector);
    },

    getSelectByBlock: function (block) {
        return Element.down(block, this.config.countrySelectorClass);
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

    init: function () {
        this.countrySelectors = $$(this.config.countrySelectorClass);

        this.countrySelectors.each(function (el) {
            Event.observe(el, 'change', this.onCountryChange.bind(this, el));
        }.bind(this))

    }
});