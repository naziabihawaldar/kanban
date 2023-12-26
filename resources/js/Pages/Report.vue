<script setup>
import Datepicker from 'vue3-datepicker';
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import moment from 'moment';
import InputLabel from '@/Components/InputLabel.vue';
const headers = [
    { "title": "Vulnerability Name", "key": "vulnerabilityName", "align": "left", sortable: false },
    { "title": "Vulnerability ID", "key": "vulnerabilityID", "align": "left", sortable: false },
    { "title": "Vulnerability Description", "key": "vulnerabilityDescription", "align": "left", sortable: false },
    { "title": "Project", "key": "project", "align": "left", sortable: false },
    { "title": "Assigned To", "key": "assignedTo", "align": "left", sortable: false },
    { "title": "Start Date", "key": "start_date", "align": "left", sortable: false },
    { "title": "End Date", "key": "end_date", "align": "left", sortable: false },
    { "title": "Due Date", "key": "due_date", "align": "left", sortable: false },
    { "title": "Asset IP", "key": "assetIP", "align": "left", sortable: false },
    { "title": "IP & Vuln Id", "key": "ipAndVulnId", "align": "left", sortable: false },
    { "title": "Port", "key": "port", "align": "left", sortable: false },
    { "title": "Protocol", "key": "protocol", "align": "left", sortable: false },
    { "title": "OS Type/Version", "key": "osTypeVersion", "align": "left", sortable: false },
    { "title": "OS Version", "key": "osVersion", "align": "left", sortable: false },
    { "title": "Business Unit", "key": "businessUnit", "align": "left", sortable: false },
    { "title": "Class", "key": "class", "align": "left", sortable: false },
    { "title": "CVE ID", "key": "cveID", "align": "left", sortable: false },
    { "title": "CVSS Score", "key": "cvssScore", "align": "left", sortable: false },
    { "title": "Severity", "key": "severity", "align": "left", sortable: false },
    { "title": "Solution", "key": "solution", "align": "left", sortable: false },
    { "title": "Impact Of Vulnerability", "key": "impactOfVulnerability", "align": "left", sortable: false },
    { "title": "Scan Date Time", "key": "scanDateTime", "align": "left", sortable: false },
    { "title": "Background", "key": "background", "align": "left", sortable: false },
    { "title": "Service", "key": "service", "align": "left", sortable: false },
    { "title": "Remediation", "key": "remediation", "align": "left", sortable: false },
    { "title": "References", "key": "references", "align": "left", sortable: false },
    { "title": "Exception", "key": "exception", "align": "left", sortable: false },
    { "title": "Tags", "key": "tags", "align": "left", sortable: false },
    { "title": "Asset Version", "key": "assetVersion", "align": "left", sortable: false },
    { "title": "Model", "key": "model", "align": "left", sortable: false },
    { "title": "Make", "key": "make", "align": "left", sortable: false },
    { "title": "Asset Type", "key": "assetType", "align": "left", sortable: false },
    { "title": "Host Name", "key": "hostName", "align": "left", sortable: false },
    { "title": "PLK_VLAN10 (POS-SICOM) Subnet", "key": "plkVLAN10Subnet", "align": "left", sortable: false },
    { "title": "PLK_VLAN70 (Kiosk)", "key": "plkVLAN70", "align": "left", sortable: false },
    { "title": "PLK_VLAN254 (Meraki-Management)", "key": "plkVLAN254", "align": "left", sortable: false },
    { "title": "PLK_VLAN4 Subnet", "key": "plkVLAN4Subnet", "align": "left", sortable: false },
    { "title": "BK_VLAN10 (POS-SICOM) Subnet", "key": "bkVLAN10Subnet", "align": "left", sortable: false },
    { "title": "BK_VLAN70 (Kiosk)", "key": "bkVLAN70", "align": "left", sortable: false },
    { "title": "BK_VLAN254 (Meraki-Management)", "key": "bkVLAN254", "align": "left", sortable: false },
    { "title": "BK_VLAN4 Subnet", "key": "bkVLAN4Subnet", "align": "left", sortable: false },
    { "title": "THS_VLAN10 (POS-SICOM) Subnet", "key": "thsVLAN10Subnet", "align": "left", sortable: false },
    { "title": "THS_VLAN70 (Kiosk)", "key": "thsVLAN70", "align": "left", sortable: false },
    { "title": "THS_VLAN254 (Meraki-Management)", "key": "thsVLAN254", "align": "left", sortable: false },
    { "title": "THS_VLAN4 Subnet", "key": "thsVLAN4Subnet", "align": "left", sortable: false }
];
const projectList = ref('');
const loadItems = (data) => {
    serverItems.value = [];
    if(typeof data == 'undefined' )
    {
        var data = {};
        data.itemsPerPage = 15;
        data.page = 1;
        data.sortBy = null;
    }
    axios.get('/get-reports', { params: { rowsPerPage: data.itemsPerPage, query: '', page: data.page, sortBy: data.sortBy,filter:filterForm } }).then((res) => {
        if (res.data.status == 'success') {
            serverItems.value = res.data.data.data;
            totalItems.value = res.data.data.total;
            loading.value = false;
        }
    }).catch((error) => {
    })
    axios.get('/get-project-list').then((res) => {
        if (res.data.status == 'success') {
           projectList.value = res.data.data;
        }
    }).catch((error) => {
    })

};

const itemsPerPage = ref(15);
const search = ref('');
const serverItems = ref([]);
const loading = ref(true);
const exportLoading = ref(false);
const totalItems = ref(0);
const filterDialog = ref(false);
var truncatedString = function (strg) {
    if (strg != null) {
        var trn = strg.slice(0, 50);
        return trn;
    }
    return strg;
};
const exportDownload = () => {
    exportLoading.value = true;
    axios.post('/reports/export',{filter:filterForm}).then((response) => {
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

const openModal = () => {
    filterDialog.value = true;
};
const closeModal = () => {
    filterDialog.value = false;
};
//Filter Popup
const filter_select = ref(null);
const filter_keyword = ref('');
const filter_items = ref([]);
const filter_loading = ref(false);
const filter_scan_date = ref(new Date());
const filter_start_date = ref(new Date());
const filter_end_date = ref(new Date());
const filter_due_date = ref(new Date());
const chipModal = ref(false);

var obj = {};
const filterForm = useForm({
    project: '',
    assignee: '',
    severity: '',
    domain: '',
    scan_date: '',
    start_date: '',
    end_date: '',
    due_date: '',
    vulnerabilityName: '',
    ip_and_vuln_id: '',
    os_type: '',
    os_version: '',
    business_unit: '',
});
watch(filter_scan_date, (value) => {
  var date = new Date(value);
  const day = ('0' + date.getDate()).slice(-2);
  const month = date.getMonth() + 1;
  const year = date.getFullYear();
  filterForm.scan_date = year + '-' + month + '-' + day;
});
watch(filter_start_date, (value) => {
  var date = new Date(value);
  const day = ('0' + date.getDate()).slice(-2);
  const month = date.getMonth() + 1;
  const year = date.getFullYear();
  filterForm.start_date = year + '-' + month + '-' + day;
});
watch(filter_end_date, (value) => {
  var date = new Date(value);
  const day = ('0' + date.getDate()).slice(-2);
  const month = date.getMonth() + 1;
  const year = date.getFullYear();
  filterForm.end_date = year + '-' + month + '-' + day;
});
watch(filter_due_date, (value) => {
  var date = new Date(value);
  const day = ('0' + date.getDate()).slice(-2);
  const month = date.getMonth() + 1;
  const year = date.getFullYear();
  filterForm.due_date = year + '-' + month + '-' + day;
});
const filter_query = async (query) => {
    if (query != null && query.length > 1) {
        filter_loading.value = true;
        const response = await axios.post('/search-user', {
            query: query
        });
        if (response.data.length) {
            filter_items.value = response.data;
            filter_loading.value = false;
        }
    }
};
watch(filter_keyword, (v) => {
    filter_query(v);
});
const submitFilter = () => {
    console.log(JSON.stringify(filterForm));
    chipModal.value = false;
  var value = filter_select.value;
  obj = {};
  if (value !== null && typeof value === 'object') {
    filterForm.assignee = value.id;
    Object.assign(obj, { Assignee: value.name });
  }

  Object.keys(filterForm).forEach(function (key) {
    if (key == 'severity' && filterForm[key] != '') {
      Object.assign(obj, { Severity: filterForm[key] });
    }
    if (key == 'domain' && filterForm[key] != '') {
      Object.assign(obj, { Domain: filterForm[key] });
    }
    if (key == 'scan_date' && filterForm[key] != '') {
      Object.assign(obj, { ScanDate: filterForm[key] });
    }
    if (key == 'start_date' && filterForm[key] != '') {
      Object.assign(obj, { StartDate: filterForm[key] });
    }
    if (key == 'end_date' && filterForm[key] != '') {
      Object.assign(obj, { EndDate: filterForm[key] });
    }
    if (key == 'due_date' && filterForm[key] != '') {
      Object.assign(obj, { DueDate: filterForm[key] });
    }
    if (key == 'vulnerabilityName' && filterForm[key] != '') {
      Object.assign(obj, { VulnerabilityName: filterForm[key] });
    }
    if (key == 'ip_and_vuln_id' && filterForm[key] != '') {
      Object.assign(obj, { IPAndVulnId: filterForm[key] });
    }
    if (key == 'os_type' && filterForm[key] != '') {
      Object.assign(obj, { OSType: filterForm[key] });
    }
    if (key == 'os_version' && filterForm[key] != '') {
      Object.assign(obj, { OSVersion: filterForm[key] });
    }
    if (key == 'business_unit' && filterForm[key] != '') {
      Object.assign(obj, { BusinessUnit: filterForm[key] });
    }
  });
  if (Object.keys(obj).length != 0) {
    chipModal.value = true;
  }
  closeModal();
  loadItems();
//   this.$refs.vuetable.reload();
};
function resetFilter(val) {
  chipModal.value = false;
  var selVal = val.toLowerCase();
  console.log(selVal);
  if (selVal == 'assignee') {
    filter_select.value = '';
    delete obj.Assignee;
    filterForm.assignee = null;
    filterForm.reset('assignee');
  }
  if (selVal == 'severity') {
    delete obj.Severity;
    filterForm.severity = null;
    filterForm.reset('severity');
  }
  if (selVal == 'domain') {
    delete obj.Domain;
    filterForm.domain = null;
    filterForm.reset('severity');
  }

  if (selVal == 'scandate') {
    delete obj.ScanDate;
    filterForm.scan_date = null;
  }
  if (selVal == 'startdate') {
    delete obj.StartDate;
    filterForm.start_date = null;
  }
  if (selVal == 'enddate') {
    delete obj.EndDate;
    filterForm.end_date = null;
  }
  if (selVal == 'duedate') {
    delete obj.DueDate;
    filterForm.due_date = null;
  }
  if (selVal == 'businessunit') {
    delete obj.BusinessUnit;
    filterForm.business_unit = null;
  }
  if (selVal == 'ipandvulnid') {
    delete obj.IPAndVulnId;
    filterForm.ip_and_vuln_id = null;
  }

  if (selVal == 'osversion') {
    delete obj.OSVersion;
    filterForm.os_version = null;
  }
  if (selVal == 'ostype') {
    delete obj.OSType;
    filterForm.os_type = null;
  }
  if (selVal == 'vulnerabilityname') {
    delete obj.VulnerabilityName;
    filterForm.vulnerabilityName = null;
  }


  console.log(selVal);
  console.log(JSON.stringify(obj));
  console.log(JSON.stringify(filterForm));

  console.log(Object.keys(obj).length);

  if (Object.keys(obj).length != 0) {
    chipModal.value = true;
  }
  loadItems(); 
}

</script>

<template>
    <Head>
        <title>Reports</title>
    </Head>
    <AuthenticatedLayout>
        <v-container>
            <template v-if="chipModal">
                <v-chip v-for="(value, index) in obj" :key="index" class="ma-2" closable color="primary" text-color="white"
                    @click:close="resetFilter(index)">
                    {{ index }} : {{ value }}
                </v-chip>
            </template>
            <v-row no-gutters>
                <v-col cols="6">
                    <h2 class="font-black text-xl text-gray-800 leading-tight">Reports</h2>
                </v-col>
                <v-col cols="6" align="right" class="text-right">
                    <v-btn size="small" color="primary" @click="exportDownload" :disabled="exportLoading"
                        :loading="exportLoading">Export</v-btn>
                    <v-btn style="font-size: 22px;" size="small" @click="openModal" class="ma-2" variant="text"
                        icon="mdi mdi-filter-variant" color="black-lighten-4"></v-btn>
                </v-col>
                <v-col cols="12">
                    <v-data-table-server ref="vuetable" class="dense" v-model:items-per-page="itemsPerPage" :headers="headers"
                        :items-length="totalItems" :items="serverItems" :loading="loading" :search="search"
                        item-value="name" @update:options="loadItems" @request="loadItems">
                        <template v-slot:item="item">
                            <tr>
                                <td><span v-if="item.item.vulnerability_title != ''">{{
                                    truncatedString(item.item.vulnerability_title) }}</span></td>
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
                                <td><span v-if="item.item.severity != ''">{{ truncatedString(item.item.severity) }}</span>
                                </td>
                                <td><span v-if="item.item.solution != ''">{{ truncatedString(item.item.solution) }}</span>
                                </td>
                                <td><span v-if="item.item.impact_of_vulnerability != ''">{{ truncatedString(
                                    item.item.impact_of_vulnerability) }}</span></td>
                                <td><span v-if="item.item.scan_date_time != ''">{{ truncatedString(
                                    item.item.scan_date_time) }}</span></td>
                                <td><span v-if="item.item.background != ''">{{ truncatedString(
                                    item.item.background) }}</span></td>
                                <td><span v-if="item.item.service != ''">{{ truncatedString(item.item.service) }}</span>
                                </td>
                                <td><span v-if="item.item.remediation != ''">{{ truncatedString(
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
                                <td><span v-if="item.item.asset_type != ''">{{ truncatedString(item.item.asset_type)
                                }}</span></td>
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
        <v-dialog v-model="filterDialog" width="800">
            <form @submit.prevent="submitFilter">
                <v-card class="h-full">
                    <v-card-title>Filter</v-card-title>
                    <v-card-text>
                        <v-row>
                            <v-col cols="6">
                                <div>
                                    <InputLabel for="name" value="Project" />
                                    <v-select variant="outlined" density="compact"  v-model="filterForm.project"
                                        :items="projectList" item-title="title" item-value="id"></v-select>
                                </div>
                            </v-col>
                            <v-col cols="6">
                                <div>
                                    <InputLabel for="name" value="Assignee Name" />
                                    <v-combobox variant="outlined" density="compact" clearable chips color="green " v-model:search="filter_keyword"
                                        no-filter v-model="filter_select" :items="filter_items" :loading="filter_loading"
                                        item-title="name" item-value="id" @focus="() => filter_query(keyword)" />
                                </div>
                            </v-col>
                            <v-col cols="6">
                                <div>
                                    <InputLabel for="name" value="Severity" />
                                    <v-select variant="outlined" density="compact"  v-model="filterForm.severity"
                                        :items="['Critical', 'High', 'Medium', 'Low', 'Informational', 'None']"></v-select>
                                </div>
                            </v-col>
                            <v-col cols="6">
                                <div>
                                    <InputLabel for="domain" value="Domain" />
                                    <v-select variant="outlined" density="compact" v-model="filterForm.domain"
                                        :items="['System Services', 'Infrastructure']"></v-select>
                                </div>
                            </v-col>
                            <v-col cols="6">
                                <div>
                                    <InputLabel for="domain" value="Start Date" />
                                    <Datepicker variant="outlined" density="compact" style="border: 1px solid lightgrey;width:90%" :clearable="true"
                                        v-model="filter_start_date">
                                        <template v-slot:clear="{ onClear }">
                                            <v-chip @click="onClear" style="color: red;left:-35px !important">x</v-chip>
                                        </template>
                                    </Datepicker>
                                </div>
                            </v-col>
                            <v-col cols="6">
                                <div>
                                    <InputLabel for="domain" value="End Date" />
                                    <Datepicker variant="outlined" density="compact" style="border: 1px solid lightgrey;width:90%" :clearable="true"
                                        v-model="filter_end_date">
                                        <template v-slot:clear="{ onClear }">
                                            <v-chip @click="onClear" style="color: red;left:-35px !important">x</v-chip>
                                        </template>
                                    </Datepicker>
                                </div>
                            </v-col>
                            <v-col cols="6">
                                <div>
                                    <InputLabel for="domain" value="Due Date" />
                                    <Datepicker variant="outlined" density="compact" style="border: 1px solid lightgrey;width:90%" :clearable="true"
                                        v-model="filter_due_date">
                                        <template v-slot:clear="{ onClear }">
                                            <v-chip @click="onClear" style="color: red;left:-35px !important">x</v-chip>
                                        </template>
                                    </Datepicker>
                                </div>
                            </v-col>

                            <v-col cols="6">
                                <div>
                                    <InputLabel for="domain" value="Scan Date" />
                                    <Datepicker variant="outlined" density="compact" style="border: 1px solid lightgrey;width:90%" :clearable="true"
                                        v-model="filter_scan_date">
                                        <template v-slot:clear="{ onClear }">
                                            <v-chip @click="onClear" style="color: red;left:-35px !important">x</v-chip>
                                        </template>
                                    </Datepicker>
                                </div>
                            </v-col>
                            <v-col cols="6">
                                <div>
                                    <InputLabel for="domain" value="Vulnerability Name" />
                                    <v-text-field variant="outlined" density="compact" v-model="filterForm.vulnerabilityName"
                                        hide-details></v-text-field>
                                </div>
                            </v-col>

                            <v-col cols="6">
                                <div>
                                    <InputLabel for="domain" value="IP & Vulnerability ID:" />
                                    <v-text-field variant="outlined" density="compact" v-model="filterForm.ip_and_vuln_id" 
                                        hide-details></v-text-field>
                                </div>
                            </v-col>
                            <v-col cols="6">
                                <InputLabel value="OS Type" />
                                <v-text-field variant="outlined" density="compact" v-model="filterForm.os_type" 
                                    hide-details></v-text-field>
                            </v-col>

                            <v-col cols="6">
                                <InputLabel for="domain" value="OS Version:" />
                                <v-text-field variant="outlined" density="compact" v-model="filterForm.os_version" 
                                    hide-details></v-text-field>
                            </v-col>
                            <v-col cols="6">
                                <InputLabel value="Business Unit" />
                                <v-text-field variant="outlined" density="compact" v-model="filterForm.business_unit" 
                                    hide-details></v-text-field>
                            </v-col>
                        </v-row>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn size="small" text="Close" @click="closeModal"></v-btn>
                        <v-btn size="small" color="primary" text="Submit" type="submit"></v-btn>
                    </v-card-actions>
                </v-card>
            </form>
        </v-dialog>
    </AuthenticatedLayout>
</template>