vdifn.datepicker = {};

vdifn.datepicker.defaultOptions = {
    setDefaultDate: true,
    minDate: Date.create('April 16, 2014'),
    maxDate: Date.create('2 days from today'),
    format: 'MMMM D, YYYY',
    onClose: function() {
        this.config().field.blur();
    }
};

vdifn.datepicker.create = function(options) {
    return new Pikaday(Object.merge(options || {}, vdifn.datepicker.defaultOptions, false, false));
};
