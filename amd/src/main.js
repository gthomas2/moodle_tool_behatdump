require.config({
    enforceDefine: false,
    paths: {
        // Vendor code.
        "vue_2_5_16": [
            "https://cdn.jsdelivr.net/npm/vue@2.5.16/dist/vue",
            // CDN Fallback - whoop whoop!
            M.cfg.wwwroot + "/pluginfile.php/" + M.cfg.contextid + "/tool_behatdump/vendorjs/vue"
        ],
        "vuerouter_2_5_3": "https://cdn.jsdelivr.net/npm/vue-router@2.5.3/dist/vue-router", // TODO CDN FALLBACK

        // Non vendor code.
        // Note, vuedatable is not via a CDN because it has been customised.
        "vuedatatable": M.cfg.wwwroot + "/pluginfile.php/" + M.cfg.contextid + "/tool_behatdump/vendorjs/vuedatatable",
        // Vue components
        "vuecomp": [
            M.cfg.wwwroot + "/pluginfile.php/" + M.cfg.contextid + "/tool_behatdump/vue/comps"
        ],
    }
});


define(['jquery', 'vue_2_5_16', 'vuerouter_2_5_3', 'vuedatatable'], function($, Vue, VueRouter, VueDataTable) {

    Vue.use(VueRouter);
    // Note: .default is necessary when you are using require as opposed to import (require is AMD).
    Vue.use(VueDataTable.default);

    return {
        vue: null,

        getBootstrapVersion: function() {
            var version = 2; // Default is to assume 2.
            if (window.$ && window.$.fn && window.$.fn.tooltip) {
                version = window.$.fn.tooltip.Constructor.VERSION;
                if (version === undefined) {
                    version = 2; // Assume BS 2.
                }
            }
            version = parseInt(version);
            return version;
        },

        applyBootstrapClass: function() {
            $('body').addClass('bs-major-version-' + this.getBootstrapVersion());
        },

        init: function(vueOpts) {

            this.applyBootstrapClass();

            var dfd = $.Deferred();

            // It seemed neccessary to load this once the document was ready otherwise we occasionally got a blank page.
            $(document).ready(function() {
                // Default opts. No spread operator in ES5 :-(
                var opts = {
                    el: '#app',
                    router: null
                };
                for (var property in vueOpts) {
                    if (vueOpts.hasOwnProperty(property)) {
                        opts[property] = vueOpts[property];
                    }
                }
                if (opts.routes) {
                    opts.router = new VueRouter({
                        routes: opts.routes
                    });
                }

                // Register global components.
                if (opts.globalComponents) {
                    for (var compKey in opts.globalComponents) {
                        if (opts.globalComponents.hasOwnProperty(compKey)) {
                            var component = opts.globalComponents[compKey];
                            Vue.component(compKey, component);
                        }
                    }
                }

                this.vue = new Vue(opts).$mount('#app');

                dfd.resolve(this.vue);
            });

            return dfd;
        }
    };

});