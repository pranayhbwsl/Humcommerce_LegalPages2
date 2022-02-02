define([
    'jquery',
    'Humcommerce_LegalPages/js/vue',
    'Humcommerce_LegalPages/js/vue-router',
    'Humcommerce_LegalPages/js/select2'
], function ($, Vue, VueRouter, select2) {
    "use strict";
    return function(config, element) {        
        Vue.use(VueRouter);

        Vue.component('TheWizardHeader', {
            data:function(){
                return {
                    text_exit:'Exit Wizard',
                    href:''
                }
            },
            render(createElement){
                return createElement('header',{
                    staticClass:'wplegal-wizard-header'
                },[createElement('error-message'),createElement('nav',{
                    staticClass: 'wplegal-header-navigation'
                },[createElement('a',{
                    staticClass: 'wplegal-exit-button',
                    attrs: {
                        href: this.href,
                        style:"display:none"
                    }
                },[createElement('i',{
                    staticClass: 'dashicons dashicons-dismiss'
                }),createElement('span',{
                    domProps: {
                        textContent: this.text_exit
                    }
                })])]),createElement('h1',{
                    staticClass: "wplegal-wizard-logo"
                },[createElement('div',{
                    staticClass: "wplegal-logo"
                },[createElement('div',{
                    staticClass: "wplegal-bg-img"
                })])])])
            }
        });

        Vue.component('TheWizardTimeline', {
            data: function() {
                return {
                    steps: this.$parent.wizardSteps
                }
            },
            methods: {
                stepClass: function(index) {
                    var e = "wplegal-wizard-step"
                        , s = 0;
                    for (var step in this.steps)
                        this.$parent.route.name === this.steps[step] && (s = step);
                    return index < s && (e += " wplegal-wizard-step-completed"),
                    parseInt(index) === parseInt(s) && (e += " wplegal-wizard-step-active"),
                        e
                },
                lineClass: function(index) {
                    var e = "wplegal-wizard-step-line"
                        , s = 0;
                    for (var step in this.steps)
                        this.$parent.route.name === this.steps[step] && (s = step);
                    return index <= s && (e += " wplegal-wizard-line-active"),
                        e
                },
                renderElements(createElement) {
                    var html = [];
                    this.steps.forEach((value,index) => {
                        if(index > 0) {
                            html.push(createElement('div',{
                                class: this.lineClass(index)
                            },[]));
                        }
                        html.push(createElement('div', {
                            class: this.stepClass(index)
                        },[]));
                    });
                    return html;
                }
            },
            render(createElement) {
                return createElement('div',{
                    staticClass: 'wplegal-wizard-container'
                },[createElement('div',{
                    staticClass: 'wplegal-wizard-steps'
                },this.renderElements(createElement))])
            }
        });

        Vue.component('StepHeader',{
            render(createElement){
                return createElement('header',{},[createElement('h2',{
                    domProps: {
                        innerHTML: this.$parent.text_title
                    }
                }),createElement('p',{
                    staticClass: 'subtitle',
                    domProps: {
                        innerHTML: this.$parent.text_subtitle
                    }
                })])
            }
        });
        
        Vue.component('Separator',{
            render(createElement){
                return createElement('div',{
                    staticClass:'wplegal-separator'
                });
            }
        });
        Vue.component('WplegalInfoTooltip',{
            props: {
                content: String
            },
            data:function(){
                return {
                    contents: this.content,
                }
            },
            render(createElement) {
                return createElement('span',{
                        staticClass:'wplegal-info dashicons dashicons-editor-help'
                    }
                    ,[createElement('span',{
                        staticClass:'wplegal-info-text',
                        domProps: {
                            innerHTML: this.contents
                        }
                    })]
                )
            }
        });

        Vue.component('Loading',{
            render(createElement) {
                return createElement('div',{
                    staticClass: 'wplegal-loader'
                })
            }
        });

        Vue.component('WplegalTimer', {
            props: {
                step: String
            },
            data:function(){
                return {
                    count: 15,
                }
            },
            methods: {
                countDownTimer() {
                    if(this.count > 0) {
                        setTimeout(() => {
                            this.count -= 1
                            this.countDownTimer()
                        }, 1000)
                    } else {
                        this.$router.push(this.step);
                    }
                }
            },
            created: function(){
                this.countDownTimer();
            },
            render(createElement){
                var self = this;
                return createElement('span', {
                    domProps: {
                        textContent: 'You will be redirected to the previous step in '+self.count+' seconds or '
                    }
                })
            }
        });
        Vue.component('ErrorMessage',{
            props: {
                step: String
            },
            render(createElement) {
                var self = this;
                return createElement('div',{
                    staticClass: 'wplegal-error',
                    attrs: {
                        style:"display:none"
                    }
                }, [createElement('p', {
                    domProps: {
                        textContent: 'Something went wrong, please try again later!'
                    }
                })])
            }
        });

        Vue.component('PageSectionsWizardForm',{
            data: function() {
                return {
                    formElements: [],
                    formSettings: [],
                    loading: 1
                }
            },
            methods: {
                handleSubmit: function() {
                    var self = this;
                    var data = {};
                    $('.wplegal-wizard-sections-form input[type="hidden"]').each(function(){
                        data[this.name] = this.value;
                    });
                    $('.wplegal-wizard-sections-form input[type="checkbox"]').each(function(){
                        if(this.checked) {
                            data[this.name] = this.value;
                        }
                    });
                    $('.wplegal-wizard-sections-form input[type="radio"]').each(function(){
                        if(this.checked) {
                            data[this.name] = this.value;
                        }
                    });
                    $('.wplegal-wizard-sections-form textarea').each(function(){
        
                        if( self.$root.page === 'custom_legal'){
                            var id = '#' + this.id +'_ifr';
                            data[this.name] = $(id).contents().find("body").html();
                        } else{
                            data[this.name] = this.value;
                        }
        
                    });
                    $('.wplegal-wizard-sections-form input[type="text"]').each(function(){
                        data[this.name] = this.value;
                    });
                    $('.wplegal-wizard-sections-form select').each(function(){
                        var selected = [...this.options]
                            .filter(option => option.selected)
                            .map(option => option.value);
                        this.name = this.name.slice(0,-2);
                        data[this.name] = selected;
                    });

                    var that = this;
                    $.ajax({
                        url: config.url,
                        type: "POST",
                        data: {step:'page_sections',page:that.$root.page, action:'page_sections_save', data:data},
                        showLoader: false,
                        cache: false,
                        success: function(response){
                            if(response.success){
                                that.$router.push('page_preview');
                                that.$root.route.name = 'page_preview';                                
                            }
                        },
                        error:function(){
                            $('.wplegal-wizard-button-next').prop('disabled', false);
                            $('.wplegal-wizard-button-next').removeClass('wplegal-wizard-button-loading');
                            that.$root.displayError();
                        }
                    });
                },
                handlePrev: function() {
                    this.$router.push('page_settings');
                    this.$root.route.name = 'page_settings';
                },
                labelClass: function(key) {
                    var s = '';
                    if(this.isChecked(key)) {
                        s += 'wplegal-styled-radio-label-checked'
                    }
                    return s;
                },
                titleClass: function(key) {
                    var s = 'wplegal-styled-radio';
                    if(this.isChecked(key)) {
                        s += ' wplegal-styled-radio-checked';
                    }
                    return s;
                },
                labelBoxClass: function(key) {
                    var s = '';
                    if(this.isChecked(key)) {
                        s += 'wplegal-styled-checkbox-label-checked'
                    }
                    return s;
                },
                titleBoxClass: function(key) {
                    var s = 'wplegal-styled-checkbox';
                    if(this.isChecked(key)) {
                        s += ' wplegal-styled-checkbox-checked';
                    }
                    return s;
                },
                isChecked:function(key) {
                    if(this.formSettings[key].checked) {
                        return true;
                    }
                    return false;
                },
                collapsibleClass:function(field) {
                    var s = ' ';
                    if(this.isCollapsible(field)) {
                        s += 'wplegal-collapsible wplegal-collapsible-hide'
                    }
                    return s;
                },
                isCollapsible:function(field) {
                  if(field.collapsible) {
                      return true;
                  }
                  return false;
                },
                updateSettings: function(event){
                    var id = event.target.id;
                    var parent = $('#'+id).parents('div.wplegal-settings-input-radio');
                    var ids = [];
                    parent.siblings('div.wplegal-settings-input-radio').each(function() {
                        var ele = $(this).find('input[type="radio"]');
                        ids.push(ele.attr('id'));
                    });
                    ids.forEach((value, index) => {
                        this.formSettings[value].checked = false;
                    });
                    this.formSettings[id].checked = true;
                },
                updateBoxSettings: function(key){
                    this.formSettings[key].checked = !this.formSettings[key].checked;
                },
                updateFormSettings: function(key, selected) {
                    var sub_fields = this.formSettings[key].sub_fields;
                    for(var index in sub_fields) {
                        var field = sub_fields[index];
                        field.checked = false;
                        sub_fields[index] = field;
                    }
                    for(var k in selected) {
                        var option = selected[k];
                        sub_fields[option].checked = true;
                    }
                    this.formSettings[key].sub_fields = sub_fields;
                },
                sortOptions: function(options) {
                    return Object.keys(options).sort().reduce(function (result, key) {
                        result[key] = options[key];
                        return result;
                    }, {});
                },
                createSelectOptions:function(createElement, options) {
                    var self = this;
                    options = self.sortOptions(options);
                    var html = [];
                    for(var key in options) {
                        var option = options[key];
                        var el = createElement('option',{
                            attrs: {
                                value: option.value
                            },
                            domProps: {
                                selected:option.checked,
                                textContent: option.title
                            }
                        });
                        html.push(el);
                    }
                    return html;
                },
                createFormFields: function(createElement, fields) {
                    var self = this;
                    var html = [];
                    if(fields) {
                        for(var key in fields){
                            var field = fields[key];
                            if(field.type == 'section') {
                                if(this.isCollapsible(field)) {
                                    var e = createElement('div',{
                                        class: 'wplegal-form-row wplegal-section' + this.collapsibleClass(field)
                                    },[createElement('span',{
                                        staticClass: 'dashicons dashicons-arrow-down-alt2',
                                        on: {
                                            click: function(event) {
                                                event.preventDefault();
                                                var parent = event.target.parentNode;
                                                if($(parent).hasClass('wplegal-collapsible-hide')) {
                                                    $(parent).removeClass('wplegal-collapsible-hide');
                                                    $(parent).addClass('wplegal-collapsible-show');
                                                    $(parent).find('> span.dashicons').removeClass('dashicons-arrow-down-alt2');
                                                    $(parent).find('> span.dashicons').addClass('dashicons-arrow-up-alt2');
                                                } else {
                                                    $(parent).removeClass('wplegal-collapsible-show');
                                                    $(parent).addClass('wplegal-collapsible-hide');
                                                    $(parent).find('> span.dashicons').removeClass('dashicons-arrow-up-alt2');
                                                    $(parent).find('> span.dashicons').addClass('dashicons-arrow-down-alt2');
                                                }
                                                return event;
                                            }
                                        }
                                    }),createElement('div',{
                                        attrs: {
                                            id: field.id
                                        },
                                        staticClass: 'wplegal-form-label'
                                    },[createElement('label',{
                                        domProps: {
                                            innerHTML: field.title
                                        }
                                    }),field.description != '' ? createElement('wplegal-info-tooltip',{
                                        attrs: {
                                            content: field.description
                                        }
                                    }) : []]), createElement('fieldset',{},[self.createFormFields(createElement, field.sub_fields)])]);
                                } else {
                                    var e = createElement('div',{
                                        class: 'wplegal-form-row wplegal-section' + this.collapsibleClass(field)
                                    },[createElement('div',{
                                        attrs: {
                                            id: field.id
                                        },
                                        staticClass: 'wplegal-form-label'
                                    },[createElement('label',{
                                        domProps: {
                                            innerHTML: field.title
                                        }
                                    }),field.description != '' ? createElement('wplegal-info-tooltip',{
                                        attrs: {
                                            content: field.description
                                        }
                                    }) : []]), createElement('fieldset',{},[self.createFormFields(createElement, field.sub_fields)])]);
                                }
                            }
                            if(field.type == 'input') {
                                var e = (createElement('div',{
                                    staticClass:'settings-input-text'
                                },[createElement('label',{},[createElement('span',{
                                    staticClass:'wplegal-dark',
                                    domProps: {
                                        innerHTML: field.title
                                    }
                                }),field.description != '' ? createElement('wplegal-info-tooltip',{
                                    attrs: {
                                        content: field.description
                                    }
                                }) : []]),createElement('div',{
                                    staticClass:'settings-input-text-input'
                                },[createElement('input',{
                                    attrs: {
                                        type:'text',
                                        name:field.name,
                                        id: field.id
                                    },
                                    domProps: {
                                        value:field.value
                                    }
                                })])]))
                            }
                            if(field.type == 'radio') {
                                var e = (createElement('div',{
                                    staticClass:'wplegal-settings-input-radio'
                                },[createElement('label',{
                                    class:self.labelClass(key)
                                },[createElement('span',{
                                    class: self.titleClass(key)
                                }),createElement('input',{
                                    attrs:{
                                        type:field.type,
                                        name:field.name,
                                        id: field.id
                                    },
                                    domProps: {
                                        value: field.value,
                                        checked: this.isChecked(key)
                                    },
                                    on: {
                                        change: function(event) {
                                            event.preventDefault();
                                            event.target.checked = !event.target.checked;
                                            self.updateSettings(event);
                                        }
                                    }
                                }),createElement('span',{
                                    staticClass: 'wplegal-input-title',
                                    domProps: {
                                        innerHTML: field.title
                                    }
                                }),field.description != '' ? createElement('wplegal-info-tooltip',{
                                    attrs: {
                                        content: field.description
                                    }
                                }) : []]), createElement('div',{
                                    staticClass:'wplegal-sub-field'
                                },[self.createFormFields(createElement, field.sub_fields)])]))
                            }
                            if(field.type == 'checkbox') {
                                var e = (createElement('div',{
                                    staticClass:'wplegal-settings-input-checkbox'
                                },[createElement('label',{
                                    class:self.labelBoxClass(key)
                                },[createElement('span',{
                                    class: self.titleBoxClass(key)
                                }),createElement('input',{
                                    attrs:{
                                        type:field.type,
                                        name:field.name,
                                        id:field.id
                                    },
                                    domProps: {
                                        value: field.value,
                                        checked: this.isChecked(key)
                                    },
                                    on: {
                                        change: function(event) {
                                            event.preventDefault();
                                            event.target.checked = !event.target.checked;
                                            self.updateBoxSettings(event.target.name);
                                        }
                                    }
                                }),createElement('span',{
                                    staticClass: 'wplegal-input-title',
                                    domProps: {
                                        innerHTML: field.title
                                    }
                                }),field.description != '' ? createElement('wplegal-info-tooltip',{
                                    attrs: {
                                        content: field.description
                                    }
                                }) : []]), createElement('div',{
                                    staticClass: 'wplegal-sub-field'
                                },[self.createFormFields(createElement, field.sub_fields)])]))
                            }
                            if(field.type == 'select2') {
                                var e = createElement('div', {
                                    staticClass: 'wplegal-form-row'
                                },[createElement('fieldset',{},[createElement('div',{
                                    staticClass: 'wplegal-settings-input-select'
                                },[createElement('label',{},[createElement('span',{
                                    staticClass:'wplegal-dark',
                                    domProps: {
                                        textContent: field.title
                                    }
                                }),field.description != '' ? createElement('wplegal-info-tooltip',{
                                    attrs: {
                                        content: field.description
                                    }
                                }) : []]),createElement('div',{
                                    staticClass:'wplegal-settings-input-select-input'
                                },[createElement('select',{
                                    staticClass: 'select2',
                                    attrs: {
                                        name:field.name+'[]',
                                        id: field.id,
                                        multiple:true
                                    }
                                },[self.createSelectOptions(createElement, field.sub_fields)])]) ])])]);
                                setTimeout(function(){
                                    $( '#'+field.id ).select2(
                                        {
                                            multiple: true,
                                            width: '100%',
                                            allowClear: false
                                        }
                                    ).on('change', function(event){
                                        event.preventDefault();
                                        var key = event.target.id;
                                        var selected = [...event.target.options]
                                            .filter(option => option.selected)
                                            .map(option => option.value);
                                        self.updateFormSettings(key, selected);
                                        return event;
                                    })
                                }, 1000);
        
                            }
                            if(field.type == 'textarea') {
                                var e = (createElement('div',{
                                    staticClass:'settings-input-textarea'
                                },[createElement('label',{},[createElement('span',{
                                    staticClass:'wplegal-dark',
                                    domProps: {
                                        innerHTML: field.label
                                    }
                                }),field.description != '' ? createElement('wplegal-info-tooltip',{
                                    attrs: {
                                        content: field.description
                                    }
                                }) : []]),createElement('div',{
                                    staticClass:'settings-input-textarea-input'
                                },[createElement('textarea',{
                                    attrs: {
                                        name:field.name,
                                        rows:5,
                                        id:field.id
                                    },
                                    domProps: {
                                        textContent:field.value
                                    }
                                })])]))
                            }
                            if(field.type == 'wpeditor') {
                                var e = (createElement('tinymce',{
                                    staticClass:'settings-input-textarea',
                                    attrs: {
                                        init : { 
                                            menubar:false,
                                            height:400,
                                            plugins: ["paste"],
                                            paste_as_text: true,
                                            branding:false
                                         },
                                        name:field.name,
                                        id: field.id,
                                        initialValue:field.value,
                                        plugins:"link table",
                                        toolbar:"undo redo | formatselect | bold italic | table | alignleft aligncenter alignright alignjustify | link bullist numlist outdent indent ",
        
                                    },
                                }));
                            }
                            html.push(e);
                        }
                    }
        
                    return html;
                },
                createFormRows: function(createElement) {
                    var self = this;
                    var html = [];
                    for(var key in self.formElements){
                        var field = self.formElements[key];
                        if(this.isCollapsible(field)) {
                            var e = createElement('div',{
                                attrs: {
                                    id: field.id
                                },
                                class: 'wplegal-form-row wplegal-clause' + this.collapsibleClass(field)
                            },[createElement('span',{
                                staticClass: 'dashicons dashicons-arrow-down-alt2',
                                on: {
                                    click: function(event) {
                                        event.preventDefault();
                                        var parent = event.target.parentNode;
                                        if($(parent).hasClass('wplegal-collapsible-hide')) {
                                            $(parent).removeClass('wplegal-collapsible-hide');
                                            $(parent).addClass('wplegal-collapsible-show');
                                            $(parent).find('> span.dashicons').removeClass('dashicons-arrow-down-alt2');
                                            $(parent).find('> span.dashicons').addClass('dashicons-arrow-up-alt2');
                                        } else {
                                            $(parent).removeClass('wplegal-collapsible-show');
                                            $(parent).addClass('wplegal-collapsible-hide');
                                            $(parent).find('> span.dashicons').removeClass('dashicons-arrow-up-alt2');
                                            $(parent).find('> span.dashicons').addClass('dashicons-arrow-down-alt2');
                                        }
                                        return event;
                                    }
                                }
                            }),createElement('div',{
                                staticClass:'wplegal-settings-input-checkbox'
                            },[createElement('label',{
                                class:self.labelBoxClass(key)
                            },[createElement('span',{
                                class: self.titleBoxClass(key)
                            }),createElement('input',{
                                attrs:{
                                    type:'checkbox',
                                    name:field.id
                                },
                                domProps: {
                                    value: field.value,
                                    checked: this.isChecked(key)
                                },
                                on: {
                                    change: function(event) {
                                        event.preventDefault();
                                        event.target.checked = !event.target.checked;
                                        self.updateBoxSettings(event.target.name);
                                    }
                                }
                            }),createElement('span',{
                                domProps: {
                                    innerHTML: field.title
                                }
                            }),field.description != '' ? createElement('wplegal-info-tooltip',{
                                attrs: {
                                    content: field.description
                                }
                            }) : []])]),field.fields ? self.createFormFields(createElement, field.fields): []])
                        } else {
                            var e = createElement('div',{
                                attrs: {
                                    id: field.id
                                },
                                class: 'wplegal-form-row wplegal-clause' + this.collapsibleClass(field)
                            },[createElement('div',{
                                staticClass:'wplegal-form-label'
                            },[createElement('label',{
                                domProps: {
                                    innerHTML: field.title
                                }
                            }),field.description != '' ? createElement('wplegal-info-tooltip',{
                                attrs: {
                                    content: field.description
                                }
                            }) : []]),createElement('input',{
                                attrs: {
                                    type:'hidden',
                                    value:'1',
                                    name:field.id
                                }
                            }),field.fields ? self.createFormFields(createElement, field.fields): []]);
                        }
                        html.push(e);
                    }
                    return html;
                },
                configureSettings: function(fields) {
                    for(var key in fields) {
                        var field = fields[key];
                        var sub_fields = field.sub_fields;
                        if(field.type == 'checkbox' || field.type == 'radio') {
                            this.formSettings[key] = field;
                        } else if(field.type == 'select' || field.type == 'select2') {
                            this.formSettings[key] = field;
                        }
                        this.configureSettings(sub_fields);
                    }
                }
            },
            created: function(){
                this.$parent.hasError = !1;
                const that = this;
                $.ajax({
                    url: config.url,
                    type: "POST",
                    data: {step:'page_sections',page:that.$root.page, action:'get_policy_settings'},
                    showLoader: false,
                    cache: false,
                    success: function(response){
                        that.formElements = response.data;
                        for(var key in that.formElements) {
                            var field = that.formElements[key];
                            that.formSettings[key] = field;

                            that.configureSettings(field.fields);        
                    }

                    that.loading = !1;

                    },
                    error:function(){
                        that.$router.push('page_settings');
                        that.$root.route.name = 'page_settings';
                        that.loading = !1;
                        that.$root.displayError();
                    }
                });
            },
            updated: function () {
                this.$nextTick(function () {
                    this.loading = !1;
                })
            },
            render(createElement){
                var self = this;
                return this.loading ? createElement('loading') : createElement('form',{
                    staticClass:'wplegal-wizard-sections-form',
                    on: {
                        submit: function(e) {
                            $('.wplegal-wizard-button-next').prop('disabled', true);
                            $('.wplegal-wizard-button-next').addClass('wplegal-wizard-button-loading');
                            return e.preventDefault(),
                                self.handleSubmit(e);
                        }
                    }
                },[self.createFormRows(createElement),createElement('Separator'),createElement('div',{
                    staticClass:'wplegal-form-row wplegal-form-buttons'
                },[ createElement('button',{
                    staticClass: "wplegal-wizard-button wplegal-wizard-button-prev wplegal-wizard-button-large",
                    attrs: {
                        type: "",
                        name: "prev_step"
                    },
                    on: {
                        click: function(e) {
                            return e.preventDefault(),
                                self.handlePrev(e);
                        }
                    },
                    domProps: {
                        textContent: this.$parent.text_prev
                    }
                }),createElement('button',{
                    staticClass: "wplegal-wizard-button wplegal-wizard-button-next wplegal-wizard-button-large",
                    attrs: {
                        type: "submit",
                        name: "next_step"
                    },
                    domProps: {
                        textContent: this.$parent.text_next
                    }
                })])]);
            }
        });
        
        


        Vue.component('PagePreviewWizardForm',{
            data: function() {
                return {
                    previewText: '',
                    loading: 1
                }
            },
            methods: {
                handleSubmit: function() {

                    var that = this;
                    $.ajax({
                        url: config.url,
                        type: "POST",
                        data: {step:'page_preview',page:that.$root.page, action:'page_preview_save'},
                        showLoader: false,
                        cache: false,
                        success: function(response){
                            
                            window.open(
                                response.url
                            );
                            window.location.href = response.legalpageUrl;
                        },
                        error:function(){

                            that.$root.displayError();
                        }
                    });
                },
                handlePrev: function() {
                    this.$router.push('page_sections');
                    this.$root.route.name = 'page_sections';
                }
            },
            created: function(){
                this.$parent.hasError = !1;

                const that = this;
                $.ajax({
                    url: config.url,
                    type: "POST",
                    data: {step:'page_preview',page:that.$root.page, action:'page_preview'},
                    showLoader: false,
                    cache: false,
                    success: function(response){
                        that.previewText = response.data;

                    that.loading = !1;

                    },
                    error:function(){
                        that.$router.push('page_sections');
                        that.$root.route.name = 'page_sections';
                        that.loading = !1;
                        that.$root.displayError();
                    }
                });
            },
            updated: function () {
                this.$nextTick(function () {
                    this.loading = !1;
                })
            },
            render(createElement){
                var self = this;
                return this.loading ? createElement('loading') : createElement('form',{
                    staticClass:'wplegal-wizard-preview-form',
                    on: {
                        submit: function(e) {
                            return e.preventDefault(),
                                self.handleSubmit(e);
                        }
                    }
                },[createElement('div',{
                    staticClass:'wplegal-form-row'
                },[createElement('div', {
                    staticClass: 'wplegal-page-preview',
                    domProps: {
                        innerHTML: self.previewText
                    }
                })]), createElement('Separator'),createElement('div',{
                    staticClass:'wplegal-form-row wplegal-form-buttons'
                },[createElement('button',{
                    staticClass: "wplegal-wizard-button wplegal-wizard-button-prev wplegal-wizard-button-large",
                    attrs: {
                        type: "",
                        name: "prev_step"
                    },
                    on: {
                        click: function(e) {
                            return e.preventDefault(),
                                self.handlePrev(e);
                        }
                    },
                    domProps: {
                        textContent: this.$parent.text_prev
                    }
                }), createElement('button',{
                    staticClass: "wplegal-wizard-button wplegal-wizard-button-next wplegal-wizard-button-large",
                    attrs: {
                        type: "submit",
                        name: "next_step"
                    },
                    domProps: {
                        textContent: this.$parent.text_next
                    }
                })])]);
            }
        });

        const StepPageSections = {
 
            data:function(){
                return {
                    text_title:'Policy Preferences',
                    text_subtitle:'',
                    text_next:'Preview',
                    text_prev:'Previous'
                }
            },
            render(createElement){
                return createElement('div', {
                    staticClass:'wplegal-wizard-step-page-sections'
                },[createElement('StepHeader'),createElement('div',{
                    staticClass: 'wplegal-wizard-form'
                },[createElement('Separator'), createElement('PageSectionsWizardForm')])]);
            },
        };
        const StepPagePreview = {
            data:function(){
                return {
                    text_title:'Preview',
                    text_subtitle:'',
                    text_next:'Publish',
                    text_prev:'Previous'
                }
            },
            render(createElement){
                return createElement('div', {
                    staticClass:'wplegal-wizard-step-page-preview'
                },[createElement('StepHeader'),createElement('div',{
                    staticClass: 'wplegal-wizard-form'
                },[createElement('Separator'), createElement('PagePreviewWizardForm')])]);
            }
        };

        Vue.component('GettingStartedWizardForm',{
            data: function() {
                return {
                    formElements: [],
                    loading: 1
                }
            },
            methods: {
                handleSubmit: function() {
                    this.$router.push('page_settings');
                    this.$root.route.name = 'page_settings';
                },
                labelClass: function(value) {
                    var e = '';
                    if(this.isChecked(value)) {
                        e += 'wplegal-styled-radio-label-checked'
                    }
                    return e;
                },
                titleClass: function(value) {
                    var e = 'wplegal-styled-radio';
                    if(this.isChecked(value)) {
                        e += ' wplegal-styled-radio-checked';
                    }
                    return e;
                },
                isChecked:function(value) {
                    if(this.$root.page == value) {
                        return true;
                    }
                    return false;
                },
                updateSettings: function(value) {
                    this.$root.page = value;
                },
                createFormRows:function(createElement) {
                    var self = this;
                    var html = [];
                    this.formElements.forEach((value, index) => {
                        var buttonText = 'Create';
                        var el = createElement('label',{
                            class:self.labelClass(value.value)
                        },[createElement('span',{
                            class: self.titleClass(value.value)
                        }),createElement('input',{
                            attrs:{
                                type:value.type,
                                name:value.name,
                            },
                            domProps: {
                                value: value.value,
                                checked: this.isChecked(value.value)
                            },
                            on: {
                                change: function(e) {
                                    e.preventDefault()
                                    self.updateSettings(value.value);
                                }
                            }
                        }),createElement('img',{
                            domProps:{
                                src: config.imagesPath + '/' + value.value + '.png'
                            }
                        }),createElement('span',{
                            staticClass: 'wplegal-input-title',
                            domProps: {
                                textContent: value.label
                            }
                        }),createElement('p',{
                            staticClass: "wplegal-description",
                            domProps: {
                                textContent: value.description
                            }
                        }), createElement('span', {
                            staticClass: "wplegal-create-button-wrapper"
                        }, [createElement('span', {
                            staticClass: "wplegal-create-button",
                            domProps: {
                                textContent: buttonText
                            },
                            on: {
                                click: function(e) {
                                    self.updateSettings(value.value);
                                    return e.preventDefault(),
                                        self.handleSubmit(e);
                                }
                            }
                        })])]);
                       html.push(el);
                    });
                    return html;
                }
            },
            created: function(){
                this.$parent.hasError = !1;

                var that = this;

                $.ajax({
                    url: config.url,
                    type: "POST",
                    data: {step:'getting_started',page:that.$root.page, action:'getting_started'},
                    showLoader: false,
                    cache: false,
                    success: function(response){
                        that.formElements = response.data;

                    that.loading = !1;

                    },
                    error:function(){
                        that.$root.displayError();
                        that.loading = !1;
                    }
                });
            },
            updated: function () {
                this.$nextTick(function () {
                    this.loading = !1;
                })
            },
            render(createElement){
                var self = this;
                return this.loading ? createElement('loading') : createElement('form',{
                   staticClass:'wplegal-wizard-getting-started-form',
                   on: {
                       submit: function(e) {
                           return e.preventDefault(),
                               self.handleSubmit(e);
                       }
                   }
               },[createElement('div',{
                   staticClass: 'wplegal-form-row'
               },[createElement('div',{
                   staticClass:'wplegal-form-label'
               },[createElement('label',{
                   domProps: {
                       textContent: this.$parent.input_title
                   }
               }),createElement('p',{
                   staticClass: "wplegal-description",
                   domProps: {
                       textContent: this.$parent.input_subtitle
                   }
               })]), createElement('fieldset',{},[createElement('div',{
                   staticClass:'wplegal-settings-input-radio'
               },[self.createFormRows(createElement)])])]),createElement('Separator') ,createElement('div',{
                   staticClass:'wplegal-form-row wplegal-form-buttons'
               },[createElement('button',{
                   staticClass: "wplegal-wizard-button wplegal-wizard-button-next wplegal-wizard-button-large",
                   attrs: {
                       type: "submit",
                       name: "next_step"
                   },
                   domProps: {
                       textContent: this.$parent.text_next
                   }
               })])]);
           }
        });

        Vue.component('PageSettingsWizardForm',{
            data: function() {
                return {
                    formElements: [],
                    loading: 1,
                }
            },
            methods: {
                handleSubmit: function() {
                    var data = {};
                    $('.wplegal-wizard-settings-form input').each(function(){
                        data[this.name] = this.value;
                    });
                    $('.wplegal-wizard-settings-form select').each(function(){
                        data[this.name] = this.value;
                    });

                    var that = this;
                    $.ajax({
                        url: config.url,
                        type: "POST",
                        data: {step:'page_settings_save',page:that.$root.page, action:'page_settings_save', data:data},
                        showLoader: false,
                        cache: false,
                        success: function(response){
                            that.$router.push('page_sections');
                            that.$root.route.name = 'page_sections';    
                        that.loading = !1;
    
                        },
                        error: function(){

                            that.$root.displayError();
    
                        }
                    });
                },
                handlePrev: function() {
                    this.$router.push('/');
                    this.$root.route.name = 'getting_started';
                },
                createSelectOptions:function(createElement, options) {
                    var self = this;
                    var html = [];
                    options.forEach((value, index) => {
                        var el = createElement('option',{
                            attrs: {
                                value: value.value
                            },
                            domProps: {
                                selected:value.selected,
                                textContent: value.label
                            }
                        });
                        html.push(el);
                    });
                    return html;
                },
                createFormRows: function(createElement) {
                    var self = this;
                    var html = [];
                    this.formElements.forEach((value, index) => {
                        if(value.type == 'select') {
                            var el = createElement('div', {
                                staticClass: 'wplegal-form-row'
                            },[createElement('fieldset',{},[createElement('div',{
                                staticClass: 'wplegal-settings-input-select'
                            },[createElement('label',{},[createElement('span',{
                                staticClass:'wplegal-dark',
                                domProps: {
                                    textContent: value.label
                                }
                            })]),createElement('div',{
                                staticClass:'wplegal-settings-input-select-input'
                            },[createElement('select',{
                                staticClass: 'select',
                                attrs: {
                                    name:value.name
                                }
                            },[self.createSelectOptions(createElement, value.options)])]) ])])]);
                            setTimeout(function(){
                                $('select').select2({
                                    multiple: false,
                                    width: '100%'
                                })
                            },1000);
        
                        } else {
                            var el = createElement('div',{
                                staticClass:'wplegal-form-row'
                            },[createElement('fieldset',{},[createElement('div',{
                                staticClass:'settings-input-text'
                            },[createElement('label',{},[createElement('span',{
                                staticClass:'wplegal-dark',
                                domProps: {
                                    textContent: value.label
                                }
                            })]),createElement('div',{
                                staticClass:'settings-input-text-input'
                            },[createElement('input',{
                                attrs: {
                                    type:value.type,
                                    name:value.name
                                },
                                domProps: {
                                    value:value.value
                                }
                            })])])])]);
                        }
                        html.push(el);
                    });
                    return html;
                }
            },
            created: function(){
                this.$parent.hasError = !1;

                var that = this;
                $.ajax({
                    url: config.url,
                    type: "POST",
                    data: {step:'page_settings',page:that.$root.page, action:'page_settings'},
                    showLoader: false,
                    cache: false,
                    success: function(response){
                        that.formElements = response.data;

                    that.loading = !1;

                    },
                    error: function(){
                        that.$router.push('/');
                        that.$root.route.name = 'getting_started';
                        that.loading = !1;
                        that.$root.displayError();

                    }
                });
            },
            updated: function () {
                this.$nextTick(function () {
                    this.loading = !1;
                })
            },
            render(createElement){
                var self = this;
                return this.loading ? createElement('loading') : createElement('form',{
                    staticClass:'wplegal-wizard-settings-form',
                    attrs: {
                        method:'post'
                    },
                    on: {
                        submit: function(e) {
                            return e.preventDefault(),
                                self.handleSubmit(e);
                        }
                    }
                },[self.createFormRows(createElement),createElement('Separator'), createElement('div',{
                    staticClass:'wplegal-form-row wplegal-form-buttons'
                },[createElement('button',{
                    staticClass: "wplegal-wizard-button wplegal-wizard-button-prev wplegal-wizard-button-large",
                    attrs: {
                        type: "",
                        name: "prev_step"
                    },
                    on: {
                        click: function(e) {
                            return e.preventDefault(),
                                self.handlePrev(e);
                        }
                    },
                    domProps: {
                        textContent: this.$parent.text_prev
                    }
                }), createElement('button',{
                    staticClass: "wplegal-wizard-button wplegal-wizard-button-next wplegal-wizard-button-large",
                    attrs: {
                        type: "submit",
                        name: "next_step"
                    },
                    domProps: {
                        textContent: this.$parent.text_next
                    }
                })])]);
            }
        });

        const StepGettingStarted = {
            data:function(){
                return {
                    text_title:'Getting Started',
                    text_subtitle:'',
                    text_next:'',
                    input_title:'',
                    input_subtitle:''
                }
            },
            render(createElement){
                return createElement('div', {
                    staticClass:'wplegal-wizard-step-getting-started'
                },[createElement('StepHeader'),createElement('div',{
                    staticClass: 'wplegal-wizard-form'
                },[createElement('Separator'), createElement('GettingStartedWizardForm')])]);
            }
        };

        const StepPageSettings = {
            data:function(){
                return {
                    text_title:'Recommended Settings',
                    text_subtitle:'',
                    text_next:'Next',
                    text_prev:'Prev'
                }
            },
            render(createElement){
                return createElement('div', {
                    staticClass:'wplegal-wizard-step-page-settings'
                },[createElement('StepHeader'),createElement('div',{
                    staticClass: 'wplegal-wizard-form'
                },[createElement('Separator'), createElement('PageSettingsWizardForm')])]);
            }
        };

        
        const routes = [
            { path: '/', component: StepGettingStarted },
            { path: '/page_settings', component: StepPageSettings },
            { path: '/page_sections', component: StepPageSections },
            { path: '/page_preview', component: StepPagePreview },

        ];
        const router = new VueRouter({
            routes // short for `routes: routes`
        });
        return new Vue({
            el: '#' + element.id,
            router,
            data: {
                wizardSteps : ['getting_started', 'page_settings','page_sections','page_preview'],
                route: {
                    'name':'getting_started'
                },
                page: 'privacy_policy',
            },
            methods:{
                displayError(){
                    $('.wplegal-error').css({
                        "animation-name" : "slide-warning",
                        "display" : "block"
                    });
                    setTimeout(function(){
                        $('.wplegal-error').css({
                           "display" : "none"
                        });
                    },4000);
                }
            },
            render(createElement) {
                return createElement('div',{
                    staticClass:'wplegal-admin-page wizard'
                },[createElement('the-wizard-header'),createElement('the-wizard-timeline'),createElement('div',{
                    staticClass:'wplegal-wizard-container'
                },[createElement('div',{
                    staticClass:'wplegal-wizard-content'
                },[createElement('router-view')])])])
            }
        });
    }
});