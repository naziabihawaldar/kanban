<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import moment from 'moment';
import Datepicker from 'vue3-datepicker';
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
    { "title": "Status", "key": "due_date", "align": "left", sortable: false },
];
const itemsPerPage = ref(15);
const totalItems = ref(0);
const serverItems = ref([]);
const loading = ref(true);
const search = ref('');
const exportLoading = ref(false);
const filterDialog = ref(false);
const projectList = ref('');
const filter_keyword = ref('');
const filter_select = ref(null);
const filter_items = ref([]);
const filter_loading = ref(false);
const filter_start_date = ref(new Date());
const filter_end_date = ref(new Date());
const filter_due_date = ref(new Date());
const chipModal = ref(false);
var obj = {};
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
const filterForm = useForm({
    project: '',
    task_status: '',
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
var truncatedString = function (strg) {
    if (strg != null) {
        var trn = strg.slice(0, 50);
        return trn;
    }
    return strg;
};
const exportDownload = () => {
    exportLoading.value = true;
    axios.post('/consolidated-export',{filter:filterForm}).then((response) => {
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
const submitFilter = () => {
    chipModal.value = false;
  var value = filter_select.value;
  obj = {};
  if (value !== null && typeof value === 'object') {
    filterForm.assignee = value.id;
    Object.assign(obj, { Assignee: value.name });
  }

  Object.keys(filterForm).forEach(function (key) {
    // console.log(key);
    if (key == 'project' && filterForm[key] != '') {
      Object.assign(obj, { Project: filterForm[key] });
    }
    if (key == 'task_status' && filterForm[key] != '') {
      Object.assign(obj, { TaskStatus: filterForm[key] });
    }
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
function resetFilter(val) {
  chipModal.value = false;
  var selVal = val.toLowerCase();
  console.log(selVal);
  if (selVal == 'project') {
    filter_select.value = '';
    delete obj.Project;
    filterForm.project = null;
    filterForm.reset('project');
  }
  if (selVal == 'taskstatus') {
    filter_select.value = '';
    delete obj.TaskStatus;
    filterForm.task_status = null;
    filterForm.reset('task_status');
  }
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
    <AuthenticatedLayout>
        <Head>
        <title>Consolidated Report</title>
        </Head>
        <v-container>
            <template v-if="chipModal">
                <v-chip v-for="(value, index) in obj" :key="index" class="ma-2" closable color="primary" text-color="white"
                    @click:close="resetFilter(index)">
                    {{ index }} : {{ value }}
                </v-chip>
            </template>
            <v-row no-gutters>
                <v-col cols="6">
                    <h2 class="font-black text-xl text-gray-800 leading-tight">Consolidated Reports</h2>
                </v-col>
                <v-col cols="6" align="right" class="text-right">
                    <v-btn size="small" color="primary" @click="exportDownload" :disabled="exportLoading"
                        :loading="exportLoading">Export</v-btn>
                    <v-btn style="font-size: 22px;" size="small" @click="openModal" class="ma-2" variant="text"
                        icon="mdi mdi-filter-variant" color="black-lighten-4"></v-btn>
                </v-col>
                <v-col cols="12" class="mt-3">
                    <v-data-table-server ref="vuetable" density="compact" variant="outlined" class="dense" v-model:items-per-page="itemsPerPage" :headers="headers"
                        :items-length="totalItems" :items="serverItems" :items-per-page-options="[10, 25, 50, 100]" :loading="loading" :search="search"
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
                                <td>
                                    <span v-if="item.item.task_status != null">
                                        <v-chip size="small" color="red">Overdue</v-chip>
                                    </span>
                                    <span v-else>-</span>
                                </td>
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
                                    <InputLabel for="name" value="Task Status" />
                                    <v-select variant="outlined" density="compact"  v-model="filterForm.task_status"
                                        :items="['Overdue']"></v-select>
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