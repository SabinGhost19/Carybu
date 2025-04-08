/**
 * @package         Sourcerer
 * @version         12.0.2
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2025 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

(function() {
    'use strict';

    window.RegularLabs = window.RegularLabs || {};

    window.RegularLabs.SourcererPopup = window.RegularLabs.SourcererPopup || {

        form   : null,
        options: {},

        init: function(editor_name) {
            if ( ! parent.RegularLabs.SourcererButton) {
                document.querySelector('body').innerHTML = '<div class="alert alert-error">This page cannot function on its own.</div>';
                return;
            }

            const code_editor = Joomla.editors.instances['code'];

            try {
                code_editor.getValue();
            } catch (err) {
                setTimeout(() => {
                    RegularLabs.SourcererPopup.init(editor_name);
                }, 100);
                return;
            }

            this.options = parent.Joomla.getOptions ? parent.Joomla.getOptions('rl_sourcerer_button', {}) : parent.Joomla.optionsStorage.rl_sourcerer_button || {};

            const form   = document.getElementById('sourcererForm');
            form.editors = Joomla.editors.instances;

            parent.RegularLabs.SourcererButton.setForm(form);

            const source_editor = parent.Joomla.editors.instances[editor_name];

            if ( ! source_editor) {
                return;
            }
            let string = source_editor.getSelection();

            if (typeof source_editor.instance.selection !== 'undefined') {
                string = source_editor.instance.selection.getContent();
                string = this.prepareHtml(string);
            }

            string = this.prepareText(string);
            this.setAttributes(string);
            string = this.removeSourceTags(string);

            code_editor.setValue(string);
        },

        prepareHtml: function(string) {
            let regex;

            string = this.prepareText(string);

            // remove newlines / returns
            regex  = new RegExp('[\n\r]', 'gim');
            string = string.replace(regex, '');

            // replace html breaks/paragraphs with newlines
            regex  = new RegExp('(</p><p>|</?p>|<br ?/?>)', 'gim');
            string = string.replace(regex, '\n');

            string = string.trim();

            // replace tab images with normal tabs
            regex  = new RegExp('<img[^>]*src="[^"]*/tab.(svg|png)"[^>]*>', 'gim');
            string = string.replace(regex, '\t');

            // remove remaining html tags
            regex  = new RegExp('</?[a-z][^>]*>', 'gim');
            string = string.replace(regex, '');

            // replace html entities with normal characters
            string = string.replace(/(&nbsp;|&#160;)/gi, ' ');
            string = string.replace(/&lt;/gi, '<');
            string = string.replace(/&gt;/gi, '>');
            string = string.replace(/&amp;/gi, '&');

            return string;
        },

        prepareText: function(string) {
            // handle non-breaking spaces
            const regex = new RegExp(String.fromCharCode(160), 'gim');
            string      = string.replace(regex, ' ');
            // Handle tabs
            string      = string.replace(/    /g, '\t');

            return string;
        },

        setAttributes: function(string) {
            const tag_word  = this.options.syntax_word;
            const tag_start = this.options.tag_characters[0];
            const tag_end   = this.options.tag_characters[1];

            const start_tag = this.preg_quote(tag_start + tag_word) + '( .*?)' + this.preg_quote(tag_end);
            const regex     = new RegExp(start_tag, 'gim');

            if ( ! string.match(regex)) {
                return;
            }

            const attributes = this.getAttributes(regex.exec(string)[1].trim());

            if ('raw' in attributes) {
                this.setField('raw', attributes.raw.toBooleanNumber());
            }
            if ('trim' in attributes) {
                this.setField('trim', attributes.trim.toBooleanNumber());
            }
        },

        setPhpField: function(value, method) {
            this.setField('php_file', value);
            this.setField('php_include_method', method);
        },

        getAttributes: function(string) {
            const attributes = {};

            let regex = new RegExp('^0 ?');
            if (string.match(regex)) {
                attributes.raw = true;
                string         = string.replace(/^0/, '').trim();
            }

            regex = new RegExp('([a-z_-]+)="([^"]*)"', 'gim');

            if ( ! string.match(regex)) {
                return attributes;
            }

            let match = regex.exec(string);
            while (match) {
                attributes[match[1]] = match[2];
                match                = regex.exec(string);
            }

            return attributes;
        },

        removeSourceTags: function(string) {
            const tag_word  = this.options.syntax_word;
            const tag_start = this.options.tag_characters[0];
            const tag_end   = this.options.tag_characters[1];

            const start_tag = this.preg_quote(tag_start + tag_word) + '.*?' + this.preg_quote(tag_end);
            const end_tag   = this.preg_quote(tag_start + '/' + tag_word + tag_end);

            let regex = new RegExp('(' + start_tag + ')\\s*', 'gim');
            if (string.match(regex)) {
                string = string.replace(regex, '');
            }

            regex  = new RegExp('\\s*' + end_tag, 'gim');
            string = string.replace(regex, '');

            return string.trim();
        },

        preg_quote: function(str) {
            return (str + '').replace(/([\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!<>\|\:])/g, '\\$1');
        },

        setField: function(name, value) {
            const field = document.querySelector('input[name="' + name + '"],select[name="' + name + '"]');

            if ( ! field) {
                return;
            }

            if (field.getAttribute('type') === 'radio') {
                this.setRadioOption(name, value);
                return;
            }

            field.value = value;
        },

        setRadioOption: function(name, value) {
            const field = document.querySelector('input[name="' + name + '"][value="' + value + '"]');

            if ( ! field) {
                return;
            }

            field.checked = true;
        },
    };

    String.prototype.trim = function() {
        // fix linebreaks
        this.replace(/\r/, "");

        // Left trim
        this.replace(/^[\n ]*/, "");

        // Right trim
        this.replace(/[\n ]*$/, "");

        return this;
    };

    String.prototype.toBooleanNumber = function() {
        const string = this.toString().valueOf();

        return string === '1' || string === 'true' ? 1 : 0;
    };
})();
