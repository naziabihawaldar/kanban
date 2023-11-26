<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import moment from 'moment';

const headers = [
    { "title": "Vulnerability Name", "key": "vulnerabilityName", "align": "left" },
    { "title": "Vulnerability ID", "key": "vulnerabilityID", "align": "left" },
    { "title": "Vulnerability Description", "key": "vulnerabilityDescription", "align": "left" },
    { "title": "Board", "key": "board", "align": "left" },
    { "title": "Assigned To", "key": "assignedTo", "align": "left" },
    { "title": "Start Date", "key": "start_date", "align": "left" },
    { "title": "End Date", "key": "end_date", "align": "left" },
    { "title": "Due Date", "key": "due_date", "align": "left" },
    { "title": "Asset IP", "key": "assetIP", "align": "left" },
    { "title": "IP & Vuln Id", "key": "ipAndVulnId", "align": "left" },
    { "title": "Port", "key": "port", "align": "left" },
    { "title": "Protocol", "key": "protocol", "align": "left" },
    { "title": "OS Type/Version", "key": "osTypeVersion", "align": "left" },
    { "title": "OS Version", "key": "osVersion", "align": "left" },
    { "title": "Business Unit", "key": "businessUnit", "align": "left" },
    { "title": "Class", "key": "class", "align": "left" },
    { "title": "CVE ID", "key": "cveID", "align": "left" },
    { "title": "CVSS Score", "key": "cvssScore", "align": "left" },
    { "title": "Severity", "key": "severity", "align": "left" },
    { "title": "Solution", "key": "solution", "align": "left" },
    { "title": "Impact Of Vulnerability", "key": "impactOfVulnerability", "align": "left" },
    { "title": "Scan Date Time", "key": "scanDateTime", "align": "left" },
    { "title": "Background", "key": "background", "align": "left" },
    { "title": "Service", "key": "service", "align": "left" },
    { "title": "Remediation", "key": "remediation", "align": "left" },
    { "title": "References", "key": "references", "align": "left" },
    { "title": "Exception", "key": "exception", "align": "left" },
    { "title": "Tags", "key": "tags", "align": "left" },
    { "title": "Asset Version", "key": "assetVersion", "align": "left" },
    { "title": "Model", "key": "model", "align": "left" },
    { "title": "Make", "key": "make", "align": "left" },
    { "title": "Asset Type", "key": "assetType", "align": "left" },
    { "title": "Host Name", "key": "hostName", "align": "left" },
    { "title": "PLK_VLAN10 (POS-SICOM) Subnet", "key": "plkVLAN10Subnet", "align": "left" },
    { "title": "PLK_VLAN70 (Kiosk)", "key": "plkVLAN70", "align": "left" },
    { "title": "PLK_VLAN254 (Meraki-Management)", "key": "plkVLAN254", "align": "left" },
    { "title": "PLK_VLAN4 Subnet", "key": "plkVLAN4Subnet", "align": "left" },
    { "title": "BK_VLAN10 (POS-SICOM) Subnet", "key": "bkVLAN10Subnet", "align": "left" },
    { "title": "BK_VLAN70 (Kiosk)", "key": "bkVLAN70", "align": "left" },
    { "title": "BK_VLAN254 (Meraki-Management)", "key": "bkVLAN254", "align": "left" },
    { "title": "BK_VLAN4 Subnet", "key": "bkVLAN4Subnet", "align": "left" },
    { "title": "THS_VLAN10 (POS-SICOM) Subnet", "key": "thsVLAN10Subnet", "align": "left" },
    { "title": "THS_VLAN70 (Kiosk)", "key": "thsVLAN70", "align": "left" },
    { "title": "THS_VLAN254 (Meraki-Management)", "key": "thsVLAN254", "align": "left" },
    { "title": "THS_VLAN4 Subnet", "key": "thsVLAN4Subnet", "align": "left" }
];
const loadItems = (data) => {
    serverItems.value = [];
    var pagination = { sortBy: data.sortBy, descending: true, rowsPerPage: data.itemsPerPage, query: '', page: data.page };
    axios.get('/get-reports', { params: { rowsPerPage: data.itemsPerPage, query: '', page: data.page } }).then((res) => {
        if (res.data.status == 'success') {
            //console.log(JSON.stringify(res.data));
            serverItems.value = res.data.data.data;
            totalItems.value = res.data.data.total;
            loading.value = false;
        }
    }).catch((error) => {
        // console.log(error);
    })
};

const itemsPerPage = ref(15);
const search = ref('');
const serverItems = ref([]);
const loading = ref(true);
const exportLoading = ref(false);
const totalItems = ref(0);
var truncatedString = function (strg) {
    if (strg != null) {
        var trn = strg.slice(0, 50);
        return trn;
    }
    return strg;
};
const exportDownload = () => {
    exportLoading.value = true;
    axios.post('/reports/export').then((response) => {
        download(response.data, 'reports');
    }).catch((error) => {
        console.log(error);
    }).finally(() => {
        exportLoading.value = false;
        // this.loaders.export_resource_booking = false;
    });
};

const download = (data, file_name) => {
    const url = window.URL.createObjectURL(new Blob([data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', file_name + Date.now() + '.' + 'csv');
    document.body.appendChild(link);
    link.click();
};

</script>
<template>
    <Head>
        <title>Reports</title>
    </Head>
    <AuthenticatedLayout>
        <v-container>
            <v-row no-gutters>
                <v-col cols="6">
                    <h2 class="font-black text-xl text-gray-800 leading-tight">Reports</h2>
                </v-col>
                <v-col cols="6" align="right" class="text-right">
                    <v-btn size="small" color="primary" @click="exportDownload" :disabled="exportLoading"
                        :loading="exportLoading">Export</v-btn>
                    <v-btn style="font-size: 22px;" size="small" class="ma-2" variant="text" icon="mdi mdi-filter-variant"
                        color="black-lighten-4"></v-btn>
                </v-col>
                <v-col cols="12">
                    <v-data-table-server class="dense" v-model:items-per-page="itemsPerPage" :headers="headers"
                        :items-length="totalItems" :items="serverItems" :loading="loading" :search="search"
                        item-value="name" @update:options="loadItems">
                        <template v-slot:item="item">
                            <tr>
                                <td><span v-if="item.item.vulnerability_title != ''">{{ truncatedString(item.item.vulnerability_title) }}</span></td>
                                <td><span v-if="item.item.vulnerability_id != ''">{{ truncatedString(
                                    item.item.vulnerability_id) }}</span></td>
                                <td><span v-if="item.item.vulnerability_desc != ''">{{ truncatedString(
                                    item.item.vulnerability_desc) }}</span></td>
                                <td> <span v-if="item.item.board">{{ item.item.board.title }}</span> </td>
                                <td> <span v-if="item.item.users.length > 0">{{ item.item.users[0].name }}</span><span
                                        v-else> - </span> </td>
                                <td> <span v-if="moment(item.item.start_date).isValid()">{{
                                    moment(item.item.start_date).format('D MMM YY') }}</span><span v-else> - </span>
                                </td>
                                <td> <span v-if="moment(item.item.end_date).isValid()">{{
                                    moment(item.item.end_date).format('D MMM YY') }}</span><span v-else> - </span></td>
                                <td><span v-if="moment(item.item.due_date).isValid()">{{
                                    moment(item.item.due_date).format('D MMM YY') }}</span><span v-else> - </span></td>
                                <td><span v-if="item.item.asset_ip != ''">{{ truncatedString(item.item.asset_ip) }}</span>
                                </td>
                                <td><span v-if="item.item.ip_and_vuln_id != ''">{{ truncatedString(
                                    item.item.ip_and_vuln_id) }}</span></td>
                                <td><span v-if="item.item.port != ''">{{ truncatedString(item.item.port) }}</span></td>
                                <td><span v-if="item.item.protocol != ''">{{ truncatedString(item.item.protocol) }}</span>
                                </td>
                                <td><span v-if="item.item.os_type != ''">{{ truncatedString(item.item.os_type) }}</span>
                                </td>
                                <td><span v-if="item.item.os_version != ''">{{ truncatedString(
                                    item.item.os_version) }}</span></td>
                                <td><span v-if="item.item.business_unit != ''">{{ truncatedString(
                                    item.item.business_unit) }}</span></td>
                                <td><span v-if="item.item.class != ''">{{ truncatedString(item.item.class) }}</span>
                                </td>
                                <td><span v-if="item.item.cve_id != ''">{{ truncatedString(item.item.cve_id) }}</span>
                                </td>
                                <td><span v-if="item.item.cvss_score != ''">{{ truncatedString(
                                    item.item.cvss_score) }}</span></td>
                                <td><span  v-if="item.item.severity != ''">{{ truncatedString(item.item.severity) }}</span>
                                </td>
                                <td><span v-if="item.item.solution != ''">{{ truncatedString(item.item.solution) }}</span>
                                </td>
                                <td><span v-if="item.item.impact_of_vulnerability != ''">{{ truncatedString(
                                    item.item.impact_of_vulnerability) }}</span></td>
                                <td><span v-if="item.item.scan_date_time != ''">{{ truncatedString(
                                    item.item.scan_date_time) }}</span></td>
                                <td><span v-if="item.item.background != ''">{{ truncatedString(
                                    item.item.background) }}</span></td>
                                <td><span v-if="item.item.service != ''" >{{ truncatedString(item.item.service) }}</span>
                                </td>
                                <td><span v-if="item.item.remediation != ''" >{{ truncatedString(
                                    item.item.remediation) }}</span></td>
                                <td><span v-if="item.item.references != ''">{{ truncatedString(
                                    item.item.references) }}</span></td>
                                <td><span v-if="item.item.exception != ''">{{ truncatedString(item.item.exception) }}</span>
                                </td>
                                <td><span v-if="item.item.tags != ''">{{ truncatedString(item.item.tags) }}</span></td>
                                <td><span v-if="item.item.asset_version != ''">{{ truncatedString(
                                    item.item.asset_version) }}</span></td>
                                <td><span v-if="item.item.model != ''">{{ truncatedString(item.item.model) }}</span>
                                </td>
                                <td><span v-if="item.item.make != ''">{{ truncatedString(item.item.make) }}</span></td>
                                <td><span v-if="item.item.asset_type != ''">{{ truncatedString(item.item.asset_type) }}</span></td>
                                <td><span v-if="item.item.host_name != ''">{{ truncatedString(item.item.host_name) }}</span>
                                </td>
                                <td><span v-if="item.item.PLK_VLAN10_POS_SICOM_Subnet != ''">{{ truncatedString(
                                    item.item.PLK_VLAN10_POS_SICOM_Subnet) }}</span></td>
                                <td><span v-if="item.item.PLK_VLAN70_Kiosk_Subnet != ''">{{ truncatedString(
                                    item.item.PLK_VLAN70_Kiosk_Subnet) }}</span></td>
                                <td><span v-if="item.item.PLK_VLAN254_Meraki_Management_Subnet != ''">{{ truncatedString(
                                    item.item.PLK_VLAN254_Meraki_Management_Subnet) }}</span></td>
                                <td><span v-if="item.item.PLK_VLAN4_Subnet != ''">{{ truncatedString(
                                    item.item.PLK_VLAN4_Subnet) }}</span></td>
                                <td><span v-if="item.item.BK_VLAN10_POS_SICOM_Subnet != ''">{{ truncatedString(
                                    item.item.BK_VLAN10_POS_SICOM_Subnet) }}</span></td>
                                <td><span v-if="item.item.BK_VLAN70_Kiosk_Subnet != ''">{{ truncatedString(
                                    item.item.BK_VLAN70_Kiosk_Subnet) }}</span></td>
                                <td><span v-if="item.item.BK_VLAN254_Meraki_Management_Subnet != ''">{{ truncatedString(
                                    item.item.BK_VLAN254_Meraki_Management_Subnet) }}</span></td>
                                <td><span v-if="item.item.BK_VLAN4_Subnet != ''">{{ truncatedString(
                                    item.item.BK_VLAN4_Subnet) }}</span></td>
                                <td><span v-if="item.item.THS_VLAN10_POS_SICOM_Subnet != ''">{{ truncatedString(
                                    item.item.THS_VLAN10_POS_SICOM_Subnet) }}</span></td>
                                <td><span v-if="item.item.THS_VLAN70_Kiosk_Subnet != ''">{{ truncatedString(
                                    item.item.THS_VLAN70_Kiosk_Subnet) }}</span></td>
                                <td><span v-if="item.item.THS_VLAN254_Meraki_Management_Subnet != ''">{{ truncatedString(
                                    item.item.THS_VLAN254_Meraki_Management_Subnet) }}</span></td>
                                <td><span v-if="item.item.THS_VLAN4_Subnet != ''">{{ truncatedString(
                                    item.item.THS_VLAN4_Subnet) }}</span></td>

                            </tr>
                        </template>
                </v-data-table-server>
            </v-col>
        </v-row>
    </v-container>

</AuthenticatedLayout></template>