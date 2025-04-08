/**
 * @package         Regular Labs Library
 * @version         25.3.16992
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2025 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

(function() {
    'use strict';

    window.RegularLabs = window.RegularLabs || {};

    window.RegularLabs.Scripts = window.RegularLabs.Scripts || {
        version: '25.3.16992',

        ajax_list        : [],
        started_ajax_list: false,
        ajax_list_timer  : null,

        loadAjax: function(url, success, fail, query, timeout, dataType, cache) {
            if (url.indexOf('index.php') !== 0 && url.indexOf('administrator/index.php') !== 0) {
                url = url.replace('http://', '');
                url = `index.php?rl_qp=1&url=${encodeURIComponent(url)}`;
                if (timeout) {
                    url += `&timeout=${timeout}`;
                }
                if (cache) {
                    url += `&cache=${cache}`;
                }
            }

            let base = window.location.pathname;

            base = base.substring(0, base.lastIndexOf('/'));

            if (
                typeof Joomla !== 'undefined'
                && typeof Joomla.getOptions !== 'undefined'
                && Joomla.getOptions('system.paths')
            ) {
                base = Joomla.getOptions('system.paths').base;
            }

            // console.log(url);
            // console.log(`${base}/${url}`);

            this.loadUrl(
                `${base}/${url}`,
                null,
                (function(data) {
                    if (success) {
                        success = `data = data ? data : ''; ${success};`.replace(/;\s*;/g, ';');
                        eval(success);
                    }
                }),
                (function(data) {
                    if (fail) {
                        fail = `data = data ? data : ''; ${fail};`.replace(/;\s*;/g, ';');
                        eval(fail);
                    }
                })
            );
        },

        /**
         * Loads a url with optional POST data and optionally calls a function on success or fail.
         *
         * @param url      String containing the url to load.
         * @param data     Optional string representing the POST data to send along.
         * @param success  Optional callback function to execute when the url loads successfully (status 200).
         * @param fail     Optional callback function to execute when the url fails to load.
         */
        loadUrl: function(url, data, success, fail) {
            return new Promise((resolve) => {
                const request = new XMLHttpRequest();

                request.open("POST", url, true);

                request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

                request.onreadystatechange = function() {
                    if (this.readyState !== 4) {
                        return;
                    }

                    if (this.status !== 200) {
                        fail && fail.call(null, this.responseText, this.status, this);
                        resolve(this);
                        return;
                    }

                    success && success.call(null, this.responseText, this.status, this);
                    resolve(this);
                };

                request.send(data);
            });
        },

        addToLoadAjaxList: function(url, success, error) {
            // wrap inside the loadajax function (and escape string values)
            url     = url.replace(/'/g, "\\'");
            success = success.replace(/'/g, "\\'");
            error   = error.replace(/'/g, "\\'");

            const action = `RegularLabs.Scripts.loadAjax(
                    '${url}',
                    '${success};RegularLabs.Scripts.ajaxRun();',
                    '${error};RegularLabs.Scripts.ajaxRun();'
                )`;

            this.addToAjaxList(action);
        },

        addToAjaxList: function(action) {
            this.ajax_list.push(action);

            if ( ! this.started_ajax_list) {
                this.ajaxRun();
            }
        },

        ajaxRun: function() {
            if ( ! this.ajax_list.length) {
                return;
            }

            clearTimeout(this.ajax_list_timer);

            this.started_ajax_list = true;

            const action = this.ajax_list.shift();

            eval(`${action};`);

            if ( ! this.ajax_list.length) {
                this.started_ajax_list = false;
                return;
            }

            // Re-trigger this ajaxRun function just in case it hangs somewhere
            this.ajax_list_timer = setTimeout(
                function() {
                    RegularLabs.Scripts.ajaxRun();
                },
                5000
            );
        },
    };
})();
