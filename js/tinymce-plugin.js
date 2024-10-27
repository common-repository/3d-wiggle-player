(function () {
    tinymce.create('tinymce.plugins.psswiggleplayer', {
        init: function (editor, url) {
            var self = this;

            this.url = url;
            this.editor = editor;

            this.constants = {
                DURATION_DEFAULT: 529,
                DURATION_MIN: 36,
                DURATION_MAX: 20000,

                PAUSE_DEFAULT: 0.06,
                PAUSE_ONOFF: 1,

                PAUSEBALANCE_DEFAULT: 0.5,
                PAUSEBALANCE_ONOFF: 0.5,

                EFFECT_DEFAULT: 94,
                EFFECT_MIN: 0,
                EFFECT_MAX: 100,

                EFFECTBALANCE_DEFAULT: 0,
                EFFECTBALANCE_MIN: -1,
                EFFECTBALANCE_MAX: 1,
                AUTOPLAY_DEFAULT: 0,
                AUTOSTOP_DEFAULT: 1,

                DIALOG_DEFAULT: 1,

                IMG_CLASS: 'psswiggleplayer',
                DATA_PREFIX: 'data-psswiggleplayer-'
            };

            editor.addCommand('psswiggleplayer_popup', function (ui, values) {
                var node = jQuery(editor.selection.getNode());
                if (node.prop("tagName") != "IMG") {
                    editor.windowManager.alert('To set the 3D wiggle parameters please select an image and try again. You can add side by side or cross-eye images from your media library.');
                    return;
                }

                var classes = node.attr("class").split(/\s+/);
                var imageId = -1;
                for (var i = 0; i < classes.length; i++) {
                    var source = classes[i].match(/wp-image-([0-9]+)/);
                    if (source && source.length > 1) {
                        imageId = parseInt(source[1]);
                        break;
                    }
                }

                // values priority:
                // 1. img attributes
                // 2. values parameter
                // 3. detault values
                if (node.hasClass(self.constants.IMG_CLASS)) {
                    values.duration = node.attr(self.constants.DATA_PREFIX + 'duration');
                    values.effect = node.attr(self.constants.DATA_PREFIX + 'effect');
                    values.balance = node.attr(self.constants.DATA_PREFIX + 'balance');
                    values.autoplay = node.attr(self.constants.DATA_PREFIX + 'autoplay');
                    values.autostop = node.attr(self.constants.DATA_PREFIX + 'autostop');
                    values.dialog = node.attr(self.constants.DATA_PREFIX + 'dialog');
                }

                values.duration = values.duration ? values.duration : self.constants.DURATION_DEFAULT;
                values.effect = values.effect ? values.effect : self.constants.EFFECT_DEFAULT;
                values.balance = values.balance ? values.balance : self.constants.EFFECTBALANCE_DEFAULT;
                values.autoplay = values.autoplay ? values.autoplay : self.constants.AUTOPLAY_DEFAULT;
                values.autostop = values.autostop ? values.autostop : self.constants.AUTOSTOP_DEFAULT;
                values.dialog = values.dialog ? values.dialog : self.constants.DIALOG_DEFAULT;

                editor.windowManager.open({
                    title: '3D Wiggle Player settings',
                    close_previous: true,
                    body: [
                        {
                            type: 'container',
                            html: '',
                            style: 'height: 20px;',
                            onPostRender: function () {
                                var content = jQuery("<p></p>").css("padding-bottom", "10px").css('text-align', 'right').append("Loading settings from image...");
                                jQuery(this.getEl()).empty().append(content);
                                var data = {
                                    action: 'psswiggleplayer_read_tags',
                                    security: psswiggleplayer.nonce,
                                    src: node.attr("src"),
                                    id: imageId
                                };
                                jQuery.post(ajaxurl, data, function (response) {
                                    content.empty();
                                    if (response.status == 1) {
                                        content.append("Settings found in image! ");
                                        jQuery("<span/>").css("cursor", "pointer").css("text-decoration", "underline").text("Set original values.").on('click', function () {
                                            jQuery("#psswiggle_player_settings_duration").val(response.params.duration);
                                            jQuery("#psswiggle_player_settings_effect").val(response.params.effect);
                                            jQuery("#psswiggle_player_settings_balance").val(response.params.balance);
                                        }).appendTo(content);
                                    } else {
                                        content.append("No wiggle settings found in image! For best results use ");
                                        content.append('<a href="http://www.3dwiggle.com" style="text-decoration: underline" target="_blank">3DWiggle</a>').append(".");
                                    }
                                });
                            }
                        },
                        {
                            type: 'textbox',
                            name: 'duration',
                            id: 'psswiggle_player_settings_duration',
                            value: values.duration.toString(),
                            label: 'Animation duration [ms]:'
                        },
                        {
                            type: 'textbox',
                            name: 'effect',
                            id: 'psswiggle_player_settings_effect',
                            value: values.effect.toString(),
                            label: 'Animation effect [100% to 0%]:'
                        },
                        {
                            type: 'textbox',
                            name: 'balance',
                            value: values.balance.toString(),
                            id: 'psswiggle_player_settings_balance',
                            label: 'Animation effect balance [-1 to +1]:'
                        },
                        {
                            type: 'checkbox',
                            name: 'autoplay',
                            checked: values.autoplay.toString() == "1",
                            id: 'psswiggle_player_settings_autoplay',
                            label: 'Start wiggle on page load:'
                        },
                        {
                            type: 'checkbox',
                            name: 'autostop',
                            checked: values.autostop.toString() == "1",
                            id: 'psswiggle_player_settings_autostop',
                            label: 'Stop wiggle on mouse leave:'
                        },
                        {
                            type: 'checkbox',
                            name: 'dialog',
                            checked: values.dialog.toString() == "1",
                            id: 'psswiggle_player_settings_dialog',
                            label: 'Show wiggle in dialog on click:'
                        },
                        {
                            type: 'container',
                            style: 'height: 40px',
                            html: '',
                            onPostRender: function () {
                                var content = jQuery("<div/>").css("border-top", "1px solid black").css("margin-top", "10px").css("padding-top", "10px").css("height", "10px");

                                var resetParams = jQuery('<div class="mce-widget mce-btn mce-first mce-btn-has-text" tabindex="-1" role="button"></div>').appendTo(content);
                                jQuery('<button role="presentation" type="button" tabindex="-1">Reset settings to default</button>').on('click', function() {
                                    jQuery("#psswiggle_player_settings_duration").val(self.constants.DURATION_DEFAULT);
                                    jQuery("#psswiggle_player_settings_effect").val(self.constants.EFFECT_DEFAULT);
                                    jQuery("#psswiggle_player_settings_balance").val(self.constants.EFFECTBALANCE_DEFAULT);
                                    jQuery("#psswiggle_player_settings_dialog").val(self.constants.DIALOG_DEFAULT);
                                }).appendTo(resetParams);

                                var removeWiggle = jQuery('<div class="mce-widget mce-btn mce-last mce-btn-has-text" style="margin-left: 5px;" tabindex="-1" role="button"></div>').appendTo(content);
                                jQuery('<button role="presentation" type="button" tabindex="-1">Remove wiggle</button>').on('click', function() {
                                    node.removeAttr(self.constants.DATA_PREFIX + 'duration');
                                    node.removeAttr(self.constants.DATA_PREFIX + 'effect');
                                    node.removeAttr(self.constants.DATA_PREFIX + 'balance');
                                    node.removeAttr(self.constants.DATA_PREFIX + 'autoplay');
                                    node.removeAttr(self.constants.DATA_PREFIX + 'autostop');
                                    node.removeAttr(self.constants.DATA_PREFIX + 'dialog');
                                    node.removeClass(self.constants.IMG_CLASS);
                                    editor.windowManager.close();
                                }).appendTo(removeWiggle);

                                jQuery(this.getEl()).empty().append(content);
                            }
                        },
                    ],
                    onsubmit: function (e) {
                        var duration = parseInt(e.data.duration);
                        if (isNaN(duration) || duration < self.constants.DURATION_MIN || duration > self.constants.DURATION_MAX) {
                            editor.windowManager.alert('Please enter a value between ' + self.constants.DURATION_MIN + ' and ' + self.constants.DURATION_MAX + ' for animation duration.');
                            return false;
                        }

                        var effect = parseInt(e.data.effect);
                        if (isNaN(effect) || effect < self.constants.EFFECT_MIN || effect > self.constants.EFFECT_MAX) {
                            editor.windowManager.alert('Please enter a value between ' + self.constants.EFFECT_MIN + " and " + self.constants.EFFECT_MAX + ' for animation effect.');
                            return false;
                        }

                        var balance = parseFloat(e.data.balance);
                        if (isNaN(balance) || balance < self.constants.EFFECTBALANCE_MIN || balance > self.constants.EFFECTBALANCE_MAX) {
                            editor.windowManager.alert('Please enter a value between ' + self.constants.EFFECTBALANCE_MIN + " and " + self.constants.EFFECTBALANCE_MAX + ' for animation effect balance.');
                            return false;
                        }

                        node.attr(self.constants.DATA_PREFIX + 'duration', duration);
                        node.attr(self.constants.DATA_PREFIX + 'effect', effect);
                        node.attr(self.constants.DATA_PREFIX + 'balance', balance);
                        node.attr(self.constants.DATA_PREFIX + 'autoplay', e.data.autoplay ? 1 : 0);
                        node.attr(self.constants.DATA_PREFIX + 'autostop', e.data.autostop ? 1 : 0);
                        node.attr(self.constants.DATA_PREFIX + 'dialog', e.data.dialog ? 1 : 0);
                        node.addClass(self.constants.IMG_CLASS);
                    }
                });

            });

            editor.addButton('psswiggleplayer_button', {
                text: '3D',
                icon: false,
                onclick: function () {
                    editor.execCommand('psswiggleplayer_popup', '', {
                        autoplay: self.constants.AUTOPLAY_DEFAULT,
                        autostop: self.constants.AUTOSTOP_DEFAULT,
                        duration: self.constants.DURATION_DEFAULT,
                        effect: self.constants.EFFECT_DEFAULT,
                        balance: self.constants.EFFECTBALANCE_DEFAULT,
                    });
                }
            });
        }

    });

    tinymce.PluginManager.add('psswiggleplayer_plugin', tinymce.plugins.psswiggleplayer);
})();