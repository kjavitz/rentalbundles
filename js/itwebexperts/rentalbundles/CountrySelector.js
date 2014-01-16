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
        var block = this.getBlockBySelect(select);
        if (block) {
            var nextBlock = this.getNextBlock(block);
            if (nextBlock) {
                Element.show(nextBlock);
            }
        }
    },

    onCountryReset: function (currentSelect) {
        while (currentSelect = this.getNextSelect(currentSelect)) {

        }
    },

    resetSelect: function (select) {
        var options = Element.select(select, 'option');
        var len = options.length;
        for (var i = 0; i < len; i++) {
            if (!options[i].value) {
                options[i].selected = true;
                return;
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