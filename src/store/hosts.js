import { reactive } from 'vue';

const Host = reactive({
    hosts: [],
    hostInfo: "",
    fatech_host_status: false,
    async fetchHosts() {
        const apiUrl = tfhb_core_apps.rest_route + 'hydra-booking/v1/hosts/lists';
        try {
            const response = await fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            });
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const hostsData = await response.json();
            // Create an object where each key-value pair corresponds to a host's user_id and their combined name
            this.hosts = hostsData.hosts.map(host => {
                return {
                    name: host.first_name + ' ' + host.last_name,
                    value: host.id.toString()
                };
            });
            this.fatech_host_status = true; 
        } catch (error) {
            console.error('Error fetching Hosts:', error);
        }
    },

    async fetchHost(HostId){
        const apiUrl = tfhb_core_apps.rest_route + 'hydra-booking/v1/hosts/' + HostId;
        try {
            const response = await fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            });
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const hostsData = await response.json();
            this.hostInfo = hostsData.host.user_id;
            
        } catch (error) {
            console.error('Error fetching Hosts:', error);
        }
    }
});

export { Host };