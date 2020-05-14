<template>
    <div>
        <main-filter
                :debug="debug"
                :options="excercisesOptions"
                :game_nid="game_nid"
                :completion_url="completion_url"
                v-on:data-update="sendData"
        ></main-filter>
        <notifications group="twelve_app"></notifications>
    </div>
</template>

<script>

    import Spinner from '../components/Spinner.vue'
    import MainFilter from './components/Filter.vue'

    const axios = require('axios');

    export default {
        props: ['excercises', 'game_nid', 'completion_url', 'debug'],
        data() {
            return {
                checkedExcercises: [],
                userData: {
                    'name': '',
                },
                isStepNextDisabled: true
            };
        },
        components: {
            Spinner,
            MainFilter,
        },
        mounted() {

        },
        methods: {

            sendData: function (checked) {
                let result_url = window.location.origin + '/node';
                let request_type = 'post';

                let data = {
                    'type': '12_bursts_result',
                    'title': {
                        'value': localStorage.twelveUserName
                    },
                    'field_when': {
                        'value': this.$props.game_nid,
                    },
                    'field_finished_items': checked,
                    'field_sub_user': {
                        'value': drupalSettings.sub_account_id
                    }
                };
          
                let result_key = this.getLocalStorageKey();
                let result_nid = localStorage.getItem(result_key);
                if (result_nid) {
                    result_url += '/' + result_nid;
                    request_type = 'patch';
                }

                if (drupalSettings.username !== '') {

                    axios({url: '/session/token'}).then(token_data => {
                        let token = token_data.data;

                        axios({
                            method: request_type,
                            url: result_url,
                            data: data,
                            headers: {
                                "X-CSRF-Token": token
                            },
                        }).then(function (response) {
                            let result_key = this.getLocalStorageKey();
                            let value = response.data.nid[0].value;
                            localStorage.setItem(result_key, value);
                        }.bind(this)).catch(function (error) {
                            //@TODO Add error handler
                        });
                    }).catch(function (error) {

                    });


                } else {

                    axios({url: '/session/token'}).then(data => {
                        let token = data.data;
                    }).catch(function (error) {

                    });

                    axios({
                        method: request_type,
                        url: result_url,
                        data: data,
                        headers: {},
                        auth: {
                            username: '12bursts_consumer',
                            password: 'e+bMS3E)}qv(rAMa'
                        }
                    }).then(function (response) {
                        let result_key = this.getLocalStorageKey();
                        let value = response.data.nid[0].value;
                        localStorage.setItem(result_key, value);
                    }.bind(this)).catch(function (error) {
                        //@TODO Add error handler
                    });

                }

            },

            getLocalStorageKey: function () {
                return 'result_node_id_for_' + this.$props.game_nid + '_' + drupalSettings.user.uid;
            }
        },
        computed: {
            excercisesOptions: function () {

                var options = {};

                for (var i in this.excercises) {
                    var item = this.excercises[i];
                    options[i] = {
                        'label': item.label,
                        'description': item.description,
                        'timer': item.timer,
                        'gif_path': item.gif,
                        'id': item.id,
                    };
                }

                return options;
            }
        }
    }
</script>