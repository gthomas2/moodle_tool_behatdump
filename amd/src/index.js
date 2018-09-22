define(
    [
        'jquery',
        'tool_behatdump/main',
        'tool_behatdump/rest',
        'vuecomp/th-Filter',
        'vuecomp/td-HTML'

    ],
    function($, main, rest, thFilter, tdHTML) {
    return {
        Vue: null,
        globalComponents: {}, // Additional global components to be registered by main bootstrapper.
        init: function() {

            var self = this;
            var dumpListData = null;

            // Routing components.
            var dataTableTemplate = function(id) {
                return '<div id="' + id + '"><datatable v-bind="data">'
                    + '</datatable></div>';
            };

            var DTMethods = {
                openScreenDump: function() {
                    alert('loading screen dump here');
                }
            };
            var dumpList = { props: ['data'], template: dataTableTemplate('dt-dumplist'), methods: DTMethods };

            // Register th, td components.
            this.globalComponents.thFilter = thFilter;
            this.globalComponents.tdHTML = tdHTML;

            rest.get('dumplist', {query: null})
            .then(function(data) {
                dumpListData = data;
                var routes = [
                    {path: '/', redirect: '/dumplist'},
                    {path: '/dumplist', component: dumpList, props: {data: dumpListData}},
                ];

                main.init({
                    data: {dumpListData: dumpListData},
                    routes: routes,
                    // TODO - is this the right way to make the datatable aware of components?
                    // TODO - Maybe we need to see if this works with components: as opposed to globalComponents:
                    globalComponents: self.globalComponents,
                    watch: {
                        'dumpListData.query': {
                            handler: function(query) {
                                rest.get('dumplist', {query: JSON.stringify(query)})
                                    .then(function(data) {
                                        dumpListData = data;
                                        // We don't need to update the columns or the query - just the data and pagination.
                                        // Updating the query would put us in a watch loop!
                                        self.Vue.dumpListData.data = dumpListData.data;
                                        self.Vue.dumpListData.total = dumpListData.total;
                                    });
                            },
                            deep: true
                        }
                    }
                }).then(function(Vue) {
                    self.Vue = Vue;
                });

            });
        }
    };
});