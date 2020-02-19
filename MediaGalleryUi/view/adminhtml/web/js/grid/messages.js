/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiElement',
    'knockout'
], function (Element, ko) {
    'use strict';

    return Element.extend({
        defaults: {
            template: 'Magento_MediaGalleryUi/grid/messages',
            messageDelay: 5,
            messages: ko.observableArray()
        },

        /**
         * Get messages
         *
         * @return {Array}
         */
        get: function () {
            return this.messages();
        },

        /**
         * Add message
         *
         * @param {String} type
         * @param {String} message
         */
        add: function (type, message) {
            this.messages.push({
                code: type,
                message: message
            });
        },

        /**
         * Clear messages
         */
        clear: function () {
            this.messages.removeAll();
        },

        /**
         * Schedule message cleanup
         *
         * @param {Number} delay
         */
        scheduleCleanup: function (delay) {
            var timerId;

            delay = delay || this.messageDelay;

            timerId = setTimeout(function () {
                clearTimeout(timerId);
                this.clear();
            }.bind(this), Number(delay) * 1000);
        }
    });
});
