<script setup>
import Datepicker from 'vue3-datepicker';
import { computed, ref, watch, reactive } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import Column from '@/Components/Kanban/Column.vue';
import ColumnCreate from '@/Components/Kanban/ColumnCreate.vue';
import ColumnByFilter from '@/Components/Kanban/ColumnByFilter.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
  board: Object,
});
var obj = {};
// const columns = computed(() => props.board?.data?.columns);
const columns = ref(props.board?.data?.columns);
const boardTitle = computed(() => props.board?.data?.title);
const importsData = computed(() => props.board?.data?.imports);
const boardID = computed(() => props.board?.data?.id);
const boardAssignees = computed(() => props.board?.data?.board_assignees);
const columnsWithOrder = ref([]);
const onReorderChange = column => {
  console.log("called inside kanban for onReorderChange");
  // columnsWithOrder.value?.push(column);
};

const onReorderCommit = () => {
  console.log("called inside kanban for onReorderCommit");
  // if (!columnsWithOrder?.value?.length) {
  //   return;
  // }
  // router.put(route('cards.reorder'), {
  //   columns: columnsWithOrder.value,
  // });
  // columnsWithOrder.value = [];
};

//Add Modal
const myDialog = ref(false);
const filterDialog = ref(false);
const deleteDialog = ref(false);
const searchloading = ref(false);
const addTaskLoading = ref(false);
const addTask_filter_loading = ref(false);
const error_msgs = ref('');
const boardSearch = ref('');

const closeModal = () => {
  myDialog.value = false;
};
const openModal = () => {
  myDialog.value = true;
};

const closeFilterModal = () => {
  filterDialog.value = false;
};
const openFilterModal = () => {
  filterDialog.value = true;
};
const closeDeleteModal = () => {
  deleteDialog.value = false;
};
const openDeleteModal = () => {
  deleteDialog.value = true;
};

//Import Form   
const form = useForm({
  name: '',
  file: [],
  board_id: boardID,
});

const deleteForm = useForm({
  fileId: '',
})
  ;
const filterForm = useForm({
  board_id: boardID,
  assignee: '',
  severity: '',
  imports: '',
  domain: '',
  scan_date: '',
  vulnerabilityName: '',
  ip_and_vuln_id: '',
  os_type: '',
  os_version: '',
  business_unit: '',
  searchtxt: '',
  assigneeUsers: '',
});
const addTaskForm = useForm({
  board_id: '',
  vulnerabilityName: '',
  vulnerabilityID: '',
  description: '',
  vulnerabilityDetails: '',
  asset_ip: '',
  ip_and_vuln_id: '',
  port: '',
  protocol: '',
  os_type: '',
  os_version: '',
  business_unit: '',
  class: '',
  cve_id: '',
  cvss_score: '',
  severity: '',
  solution: '',
  impact_of_vulnerability: '',
  scan_date_time: '',
  background: '',
  service: '',
  remediation: '',
  references: '',
  exception: '',
  tags: '',
  asset_version: '',
  model: '',
  make: '',
  asset_type: '',
  host_name: '',
  PLK_VLAN10_POS_SICOM_Subnet: '',
  PLK_VLAN70_Kiosk_Subnet: '',
  PLK_VLAN254_Meraki_Management_Subnet: '',
  PLK_VLAN4_Subnet: '',
  BK_VLAN10_POS_SICOM_Subnet: '',
  BK_VLAN70_Kiosk_Subnet: '',
  BK_VLAN254_Meraki_Management_Subnet: '',
  BK_VLAN4_Subnet: '',
  THS_VLAN10_POS_SICOM_Subnet: '',
  THS_VLAN70_Kiosk_Subnet: '',
  THS_VLAN254_Meraki_Management_Subnet: '',
  THS_VLAN4_Subnet: '',
  assignee: '',
  start_date: '',
  end_date: '',
  due_date: '',
});




const submit = () => {
  error_msgs.value = '';
  form.post('/upload-board', {
    onSuccess: (val) => {
      axios.get('/get-import-details', { params: { fileName: form.name, board_id: boardID.value } }).then((res) => {
        if (res.data.status == 'success') {
          var file_path = res.data.data.file_path;
          // download('http://localhost:8000/',file_path);
          download('https://tms.hoshey.com/', file_path);
        }
      }).catch((error) => {
        // console.log(error);
      })
      form.reset('file');
      form.file = [];
      form.name = '';
      error_msgs.value = '';
      myDialog.value = false;
      snackbar_show.value = true;
      snackbar_msg.value = "successfully uploaded";
      key_column.value = key_column.value ? false : true;
    },
    onError: (err) => {
      error_msgs.value = err;
    },
    onFinish: () => {

    },
  });
};
const download = (dataurl, file_name) => {
  const url = dataurl + '' + file_name;
  const link = document.createElement('a');
  link.href = url;
  link.setAttribute('download', file_name);
  document.body.appendChild(link);
  link.click();
};

const group_by_var = ref('');
const temp_var = ref();
const snackbar_show = ref('');
const snackbar_msg = ref('');
const chipModal = ref(false);
const filter_cards = ref();
const key_column = ref(true);

watch(group_by_var, (value) => {
  temp_var.value = value;
});

//Filter Popup
const filter_select = ref(null);
const addTask_filter_select = ref(null);
const filter_keyword = ref('');
const addTask_keyword = ref('');
const filter_items = ref([]);
const addTask_filter_items = ref([]);
const filter_data = ref([]);
const filter_loading = ref(false);
const filter_scan_date = ref();
const addTask_scan_date = ref();
const addTask_start_date = ref();
const addTask_end_date = ref();
const addTask_due_date = ref();

watch(addTask_start_date, (value) => {
  if (value != null) {
    var date = new Date(value);
    const day = ('0' + date.getDate()).slice(-2);
    const month = date.getMonth() + 1;
    const year = date.getFullYear();
    addTaskForm.start_date = year + '-' + month + '-' + day;
  }
});
watch(addTask_end_date, (value) => {
  if (value != null) {
    var date = new Date(value);
    const day = ('0' + date.getDate()).slice(-2);
    const month = date.getMonth() + 1;
    const year = date.getFullYear();
    addTaskForm.end_date = year + '-' + month + '-' + day;
  }
});
watch(addTask_due_date, (value) => {
  if (value != null) {
    var date = new Date(value);
    const day = ('0' + date.getDate()).slice(-2);
    const month = date.getMonth() + 1;
    const year = date.getFullYear();
    addTaskForm.due_date = year + '-' + month + '-' + day;
  }
});
watch(addTask_scan_date, (value) => {
  if (value != null) {
    var date = new Date(value);
    const day = ('0' + date.getDate()).slice(-2);
    const month = date.getMonth() + 1;
    const year = date.getFullYear();
    addTaskForm.scan_date_time = year + '-' + month + '-' + day;
  }
});
watch(filter_scan_date, (value) => {
  if (value != null) {
    var date = new Date(value);
    const day = ('0' + date.getDate()).slice(-2);
    const month = date.getMonth() + 1;
    const year = date.getFullYear();
    filterForm.scan_date = year + '-' + month + '-' + day;
  }

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

const addTask_filter_query = async (query) => {
  if (query != null && query.length > 1) {
    addTask_filter_loading.value = true;
    const response = await axios.post('/search-user', {
      query: query
    });
    if (response.data.length) {
      addTask_filter_items.value = response.data;
      addTask_filter_loading.value = false;
    }
  }

};

watch(boardSearch, (query) => {
  filterForm.searchtxt = '';
  if (query != null) {
    filterForm.searchtxt = query;
  }
  callFilterAPI(filterForm);

});

watch(filter_keyword, (v) => {
  filter_query(v);
});
watch(addTask_keyword, (v) => {
  addTask_filter_query(v);
});

const submitBulkDelete = () => {
  axios.post('/delete-file', deleteForm).then((res) => {
    if (res.data.status == 'success') {
      closeDeleteModal();
      deleteForm.fileId = '';
      snackbar_show.value = true;
      snackbar_msg.value = "Successfully Deleted";
      router.reload();
      key_column.value = key_column.value ? false : true;
    }
  }).catch((error) => {
    // console.log(error);
  });
};
const submitAddTask = () => {
  var value = addTask_filter_select.value;
  if (value !== null && typeof value === 'object') {
    addTaskForm.assignee = value.id;
  }
  addTaskForm.board_id = boardID.value;
  addTaskForm.post('/store-task', {
    preserveScroll: true,
    onStart: () => { },
    onSuccess: (val) => {
      addTaskForm.reset();
      addTask_filter_select.value = null;
      closeAddTaskModal();
      key_column.value = key_column.value ? false : true;
    },
    onError: (err) => { },
    onFinish: () => { },
  });
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
    if (key == 'severity' && filterForm[key] != '') {
      Object.assign(obj, { Severity: filterForm[key] });
    }
    if (key == 'domain' && filterForm[key] != '') {
      Object.assign(obj, { Domain: filterForm[key] });
    }
    if (key == 'scan_date' && filterForm[key] != '') {
      Object.assign(obj, { ScanDate: filterForm[key] });
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

    if (key == 'imports' && filterForm[key] != '') {
      Object.assign(obj, { Imports: filterForm[key] });
    }

  });
  if (Object.keys(obj).length != 0) {
    chipModal.value = true;
  }
  closeFilterModal();
  callFilterAPI(filterForm);
};

function callFilterAPI(postData) {
  filter_data.value = postData;
  key_column.value = key_column.value ? false : true;
}

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
  if (selVal == 'imports') {
    delete obj.Imports;
    filterForm.imports = null;
  }

  console.log(selVal);
  console.log(JSON.stringify(obj));
  console.log(JSON.stringify(filterForm));

  if (Object.keys(obj).length != 0) {
    chipModal.value = true;
    callFilterAPI(filterForm);
  } else {
    key_column.value = key_column.value ? false : true;
    columns.value = [];
    columns.value = props.board?.data?.columns;
  }
}


//Add Assignee Dialog
const addAssigneeDialog = ref(false);
const assignee_select = ref(boardAssignees.value);
const assignee_keyword = ref('');
const assignee_items = ref([]);

const assignee_loading = ref(false);
const closeAssigneeModal = () => {
  addAssigneeDialog.value = false;
};
const openAssigneeModal = () => {
  addAssigneeDialog.value = true;
};
const assignee_query = async (query) => {
  if (query != null && query.length > 1) {
    filter_loading.value = true;
    const response = await axios.post('/search-user', {
      query: query
    });
    if (response.data.length) {
      assignee_items.value = response.data;
      assignee_loading.value = false;
    } else {
      // data.message = 'Nothing found!';
    }
  }

};
watch(assignee_keyword, (v) => {
  assignee_query(v);
});
const submitAssignee = () => {
  var values = assignee_select.value;
  if (values !== null && typeof values === 'object') {
    let separatedArray = values.map(item => item['id']);
    axios.post('/update-board-users', { assignees: separatedArray, board_id: boardID.value }).then((res) => {
      if (res.data.status == 'success') {
        closeAssigneeModal();
        snackbar_show.value = true;
        snackbar_msg.value = "Successfully Uploaded";
        router.reload();
      }
    }).catch((error) => {
      console.log(error);
    });
  }
};

var getNameInitials = function (string) {
  var names = string.split(' '),
    initials = names[0].substring(0, 1).toUpperCase();

  if (names.length > 1) {
    initials += names[names.length - 1].substring(0, 1).toUpperCase();
  }
  return initials;
};

var required = function (v) {
  return !!v || 'Field is required'
}

var colors = ['#9C27B0', '#E91E63', '#673AB7', '#3F51B5', '#009688', '#795548'];

const columnReload = (obj) => {
  key_column.value = key_column.value ? false : true;
}
var clearBoardSearch = function () {
  filterForm.searchtxt = null;
  callFilterAPI(filterForm);
  key_column.value = key_column.value ? false : true;
};

let usr_obj = [];
const assignee_filter_items = ref([]);
const activeIndex = ref(null);
var userFilter = function (user_name, user_id ,index) {
  activeIndex.value =  true;
  if(!assignee_filter_items.value.includes(user_id))
  {
    assignee_filter_items.value.push(user_id);
  }else{
    const index = assignee_filter_items.value.findIndex(value => value ===  user_id);
    if (index !== -1) {
      assignee_filter_items.value.splice(index, 1);
    }
  }
  if(assignee_filter_items.value.length > 0)
  {
    filterForm.assigneeUsers = assignee_filter_items.value;
    callFilterAPI(filterForm);
  }else{
    resetUserFilters();
  }
};
const addTaskDialog = ref(false);

const closeAddTaskModal = () => {
  addTaskDialog.value = false;
};

const openAddTaskModal = () => {
  addTaskDialog.value = true;
};

const resetUserFilters = () => {
  assignee_filter_items.value = [];
  filterForm.assigneeUsers = null;
  key_column.value = key_column.value ? false : true;
};

</script>

<template>
  <Head>
    <title>Task Management System</title>
  </Head>
  <AuthenticatedLayout>
    <div>
      <v-row no-gutters>
        <v-col cols="12" class="pl-3">
          <h1 class=" pt-3 font-bold text-xl">{{ boardTitle }}</h1>
        </v-col>
        <v-col cols="2" align="left" class="text-left">
          <div class="ml-6 mt-2 text-lg-left left-1">
            <v-text-field v-model="boardSearch" :loading="searchloading" density="compact" variant="outlined"
              label="Search this project" append-inner-icon="mdi-magnify" single-line hide-details clearable
              @click:clear="clearBoardSearch"></v-text-field>
          </div>
        </v-col>
        <v-col cols="4" align="left" class="text-left">
          <div class="ml-6 text-lg-left left-1">
            <template v-for="(boardAssignee, index) in boardAssignees" :key="boardAssignee.id">
              <div @click="userFilter(boardAssignee.name, boardAssignee.id ,index )" :class="{ blueBorder:assignee_filter_items.includes(boardAssignee.id) }"  style="border-radius: 50%;display: inline-flex;-webkit-box-align: center;align-items: center;height: 40px;position: relative;margin-left: -10px;vertical-align: top;padding: 0px;transition: transform 0.1s ease-out 0s;border-radius: 50%;z-index: 3;cursor:pointer;">
                <div style="display: inline-block;position: relative;outline: 0px;">
                <div :style="{background:colors[Math.floor(Math.random() * colors.length)]}" class="mainAssigneeCss">
                  <div style="border-radius:50%;padding:4px 6px;" >
                    <v-tooltip activator="parent" location="bottom">{{ boardAssignee.name }}</v-tooltip>
                    {{ getNameInitials(boardAssignee.name) }}
                  </div>
                </div>
                </div>
              </div>
            </template>
            <v-btn class="ma-2" style="width: 32px;height: 32px;font-size: 14px;" color="indigo"
              icon="mdi mdi-account-plus-outline" @click="openAssigneeModal"></v-btn>
              <v-btn @click="resetUserFilters" v-if="assignee_filter_items.length > 0" variant="text" size="small" >Clear filters</v-btn>
          </div>
        </v-col>
        <v-col cols="6" align="right" class="text-right">
          <v-btn size="small" color="green-darken-3" @click="openAddTaskModal">Add Task</v-btn>
          <v-btn class="ml-3" v-if="$page.props.auth.user.roles[0].name != 'user'" size="small" color="primary"
            @click="openModal">Import</v-btn>
          <v-btn class="ml-3" v-if="$page.props.auth.user.roles[0].name != 'user'" size="small" color="red-darken-4"
            @click="openDeleteModal">Bulk Delete</v-btn>
          <v-btn style="font-size: 22px;" size="small" @click="openFilterModal" class="ma-2" variant="text"
            icon="mdi mdi-filter-variant  " color="indigo-darken-4"></v-btn>
        </v-col>
      </v-row>
      <template v-if="chipModal">
        <v-chip v-for="(value, index) in obj" :key="index" class="ma-2" closable color="primary" text-color="white"
          @click:close="resetFilter(index)">
          {{ index }} : {{ value }}
        </v-chip>
      </template>
    </div>

    <div class="pl-5 pr-5 flex-1 flex flex-col h-full overflow-hidden">
      <div class="flex-1 h-full overflow-x-auto">
        <div class="inline-flex h-full items-start space-x-4 overflow-hidden">

          <Column :boardId=boardID :boardtitle="boardTitle" :filters="filter_data" :key="key_column" />
          <div class="w-72">
            <ColumnCreate :board="board.data" @onColoumnCreated="columnReload" @reorder-change="onReorderChange"
              @reorder-commit="onReorderCommit" />
          </div>
        </div>
      </div>
    </div>

    <v-dialog width="800" v-model="addAssigneeDialog">
      <form @submit.prevent="submitAssignee">
        <v-card title="Assign Users">
          <v-card-text>
            <v-row no-gutters>
              <v-col cols="12">
                <div>
                  <InputLabel for="name" value="Assignee Name" />
                  <v-combobox outlined clearable multiple chips color="green" v-model:search="assignee_keyword" no-filter
                    v-model="assignee_select" :items="assignee_items" :loading="assignee_loading" item-title="name"
                    item-value="id" @focus="() => assignee_query(keyword)" />
                </div>
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn size="small" text="Close" @click="closeAssigneeModal"></v-btn>
            <v-btn size="small" text="Submit" type="submit"></v-btn>
          </v-card-actions>
        </v-card>
      </form>
    </v-dialog>

    <v-dialog width="800" v-model="filterDialog">
      <form @submit.prevent="submitFilter">
        <v-card title="Filter">
          <v-card-text>
            <v-row no-gutters>
              <v-col cols="5" class="mr-3">
                <div>
                  <InputLabel for="name" value="Assignee Name" />
                  <v-combobox outlined clearable chips color="green " v-model:search="filter_keyword" no-filter
                    v-model="filter_select" :items="filter_items" :loading="filter_loading" item-title="name"
                    item-value="id" @focus="() => filter_query(keyword)" />
                </div>
              </v-col>

              <v-col cols="6">
                <div>
                  <InputLabel for="name" value="Severity" />
                  <v-select outlined label="Select" v-model="filterForm.severity"
                    :items="['Critical', 'High', 'Medium', 'Low', 'Informational', 'None']"></v-select>
                </div>
              </v-col>
              <v-col cols="5" class="mr-3">
                <div>
                  <InputLabel for="domain" value="Domain" />
                  <v-select outlined v-model="filterForm.domain"
                    :items="['System Services', 'Infrastructure']"></v-select>
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel for="domain" value="Scan Date" />
                  <Datepicker style="border: 1px solid lightgrey;width:90%" :clearable="true" v-model="filter_scan_date">
                    <template v-slot:clear="{ onClear }">
                      <v-chip @click="onClear" style="color: red;left:-35px !important">x</v-chip>
                    </template>
                  </Datepicker>
                </div>
              </v-col>
              <v-col cols="5" class="mr-3">
                <div>
                  <InputLabel for="domain" value="Vulnerability Name" />
                  <v-text-field outlined v-model="filterForm.vulnerabilityName" label="Vulnerability Name"
                    hide-details></v-text-field>
                </div>
              </v-col>

              <v-col cols="6">
                <div>
                  <InputLabel for="domain" value="IP & Vulnerability ID:" />
                  <v-text-field outlined v-model="filterForm.ip_and_vuln_id" label="IP & Vulnerability ID"
                    hide-details></v-text-field>
                </div>
              </v-col>
              <v-col cols="5" class="mr-3 mt-4">
                <InputLabel value="OS Type" />
                <v-text-field outlined v-model="filterForm.os_type" label="OS Type" hide-details></v-text-field>
              </v-col>

              <v-col cols="6" class="mt-4">
                <InputLabel for="domain" value="OS Version" />
                <v-text-field outlined v-model="filterForm.os_version" label="OS Version" hide-details></v-text-field>
              </v-col>
              <v-col cols="5" class="mr-3 mt-4">
                <InputLabel value="Business Unit" />
                <v-text-field outlined v-model="filterForm.business_unit" label="Business Unit"
                  hide-details></v-text-field>
              </v-col>
              <v-col cols="6" class="mt-4">
                <InputLabel for="imports" value="Imports" />
                <v-select outlined label="Select" v-model="filterForm.imports" :items="importsData"
                  item-title="name"></v-select>
              </v-col>
            </v-row>

          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn size="small" text="Close" @click="closeFilterModal"></v-btn>
            <v-btn size="small" text="Submit" type="submit"></v-btn>
          </v-card-actions>
        </v-card>
      </form>
    </v-dialog>

    <v-dialog width="500" v-model="myDialog">
      <v-card>
        <form @submit.prevent="submit">
          <v-card title="Import">
            <v-card-text class="pb-0">
              <InputLabel for="Filename" value="File Name" />
              <v-text-field outlined label="Please Enter File name" v-model="form.name" :rules="[required]" clearable
                density="compact"></v-text-field>
              <InputError v-if="form.errors" class="mt-2" :message="form.errors.name" />
              <v-row>
                <v-col cols="12">
                  <InputLabel for="File" value="File" />
                  <v-file-input v-model="form.file" variant="outlined" label="File input"
                    density="compact"></v-file-input>
                  <InputError v-if="form.errors" class="mt-2" :message="form.errors.file" />
                  <progress v-if="form.progress" :value="form.progress.percentage" max="100">{{ form.progress.percentage
                  }}%</progress>
                </v-col>
              </v-row>
              <div style="text-align: right;color:blue;">
                <a href="/import.xlsx" download>Download Sample Template</a>
              </div>

            </v-card-text>
            <v-card-actions class="pt-0">
              <v-spacer></v-spacer>
              <v-btn size="small" text="Close" @click="closeModal" :disabled="form.processing"></v-btn>
              <v-btn size="small" text="Submit" type="submit" :disabled="form.processing"></v-btn>
            </v-card-actions>
          </v-card>
        </form>
      </v-card>
    </v-dialog>

    <v-dialog width="500" v-model="deleteDialog">
      <v-card>
        <form @submit.prevent="submitBulkDelete">
          <v-card title="Bulk Delete">
            <v-card-text class="pb-0">
              <v-row>
                <v-col cols="12">
                  <div>
                    <InputLabel for="name" value="Select File" />
                    <v-select outlined label="Select" v-model="deleteForm.fileId" :items="importsData" item-title="name"
                      item-value="id"></v-select>
                  </div>
                </v-col>
              </v-row>
            </v-card-text>
            <v-card-actions class="pt-0">
              <v-spacer></v-spacer>
              <v-btn size="small" text="Close" @click="closeDeleteModal"></v-btn>
              <v-btn size="small" color="red-darken-4" text="Delete" type="submit"></v-btn>
            </v-card-actions>
          </v-card>
        </form>
      </v-card>
    </v-dialog>

    <v-dialog width="800" v-model="addTaskDialog">
      <form @submit.prevent="submitAddTask">
        <v-card title="Add Task">
          <v-card-text>
            <v-row>
              <v-col cols="6">
                <div>
                  <InputLabel value="Vulnerability Name*" />
                  <v-text-field variant="outlined" density="compact" class="mt-2" v-model="addTaskForm.vulnerabilityName"
                    hide-details :rules="[required]"></v-text-field>
                  <InputError v-if="form.errors" class="mt-2" :message="addTaskForm.errors.vulnerabilityName" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="Vulnerability ID*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2"
                    v-model="addTaskForm.vulnerabilityID" hide-details></v-text-field>
                  <InputError v-if="form.errors" class="mt-2" :message="addTaskForm.errors.vulnerabilityID" />
                </div>
              </v-col>
              <v-col cols="6" class="pt-0">
                <div>
                  <InputLabel value="Vulnerability Description*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2"
                    v-model="addTaskForm.description" hide-details></v-text-field>
                  <InputError v-if="form.errors" class="mt-2" :message="addTaskForm.errors.description" />
                </div>
              </v-col>
              <v-col cols="6" class="pt-0">
                <div>
                  <InputLabel value="Vulnerability Details*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2"
                    v-model="addTaskForm.vulnerabilityDetails" hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.vulnerabilityDetails" />
                </div>
              </v-col>
              <v-col cols="6" class="pt-0">
                <div>
                  <InputLabel value="Asset IP*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="addTaskForm.asset_ip"
                    hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.asset_ip" />
                </div>
              </v-col>
              <v-col cols="6" class="pt-0">
                <div>
                  <InputLabel value="IP & Vuln Id*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2"
                    v-model="addTaskForm.ip_and_vuln_id" hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.ip_and_vuln_id" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="Port*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="addTaskForm.port"
                    hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.port" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="Protocol*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="addTaskForm.protocol"
                    hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.protocol" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="OS Type/Version*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="addTaskForm.os_type"
                    hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.os_type" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="Os Version*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2"
                    v-model="addTaskForm.os_version" hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.os_version" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="Business Unit*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2"
                    v-model="addTaskForm.business_unit" hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.business_unit" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="Class*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="addTaskForm.class"
                    hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.class" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="CVE ID*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="addTaskForm.cve_id"
                    hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.cve_id" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="CVSS Score*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2"
                    v-model="addTaskForm.cvss_score" hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.cvss_score" />
                </div>
              </v-col>
              <v-col cols="6" class="pb-0">
                <div>
                  <InputLabel value="Severity*" />
                  <v-select variant="outlined" density="compact" class="mt-2" required v-model="addTaskForm.severity"
                    :items="['Critical', 'High', 'Medium', 'Low', 'Informational', 'None']"></v-select>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.severity" />
                </div>
              </v-col>
              <v-col cols="6" class="pb-0">
                <div>
                  <InputLabel value="Solution*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="addTaskForm.solution"
                    hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.solution" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="Impact Of Vulnerabilty*" />
                  <v-text-field variant="outlined" density="compact" class="mt-2" required
                    v-model="addTaskForm.impact_of_vulnerability" hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2"
                    :message="addTaskForm.errors.impact_of_vulnerability" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="Scan Date Time*" />
                  <Datepicker class="w-100 mt-2" variant="outlined" density="compact"
                    style="border: 1px solid #6b7280;border-radius:5px;" v-model="addTask_scan_date">
                    <template v-slot:clear="{ onClear }">
                      <v-chip @click="onClear" style="color: red;left:-35px !important">x</v-chip>
                    </template>
                  </Datepicker>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.scan_date_time" />

                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="Background*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2"
                    v-model="addTaskForm.background" hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.background" />

                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="Service*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="addTaskForm.service"
                    hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.service" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="Remediation*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2"
                    v-model="addTaskForm.remediation" hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.remediation" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="References*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2"
                    v-model="addTaskForm.references" hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.references" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="Exception*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="addTaskForm.exception"
                    hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.exception" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="Tags*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="addTaskForm.tags"
                    hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.tags" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="Asset Version*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2"
                    v-model="addTaskForm.asset_version" hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.asset_version" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="Model*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="addTaskForm.model"
                    hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.model" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="Make*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="addTaskForm.make"
                    hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.make" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="Asset Type*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2"
                    v-model="addTaskForm.asset_type" hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.asset_type" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="Host Name*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="addTaskForm.host_name"
                    hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.host_name" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="PLK_VLAN10 (POS-SICOM) Subnet*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2"
                    v-model="addTaskForm.PLK_VLAN10_POS_SICOM_Subnet" hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2"
                    :message="addTaskForm.errors.PLK_VLAN10_POS_SICOM_Subnet" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="PLK_VLAN70 (Kiosk)*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2"
                    v-model="addTaskForm.PLK_VLAN70_Kiosk_Subnet" hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2"
                    :message="addTaskForm.errors.PLK_VLAN70_Kiosk_Subnet" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="PLK_VLAN254 (Meraki-Management)*" />
                  <v-text-field variant="outlined" density="compact" class="mt-2" required
                    v-model="addTaskForm.PLK_VLAN254_Meraki_Management_Subnet" hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2"
                    :message="addTaskForm.errors.PLK_VLAN254_Meraki_Management_Subnet" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="PLK_VLAN4 Subnet*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2"
                    v-model="addTaskForm.PLK_VLAN4_Subnet" hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.PLK_VLAN4_Subnet" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="BK_VLAN10 (POS-SICOM) Subnet*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2"
                    v-model="addTaskForm.BK_VLAN10_POS_SICOM_Subnet" hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2"
                    :message="addTaskForm.errors.BK_VLAN10_POS_SICOM_Subnet" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="BK_VLAN70 (Kiosk)*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2"
                    v-model="addTaskForm.BK_VLAN70_Kiosk_Subnet" hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2"
                    :message="addTaskForm.errors.BK_VLAN70_Kiosk_Subnet" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="BK_VLAN254 (Meraki-Management)*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2"
                    v-model="addTaskForm.BK_VLAN254_Meraki_Management_Subnet" hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2"
                    :message="addTaskForm.errors.BK_VLAN254_Meraki_Management_Subnet" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="BK_VLAN4 Subnet*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2"
                    v-model="addTaskForm.BK_VLAN4_Subnet" hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.BK_VLAN4_Subnet" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="THS_VLAN10 (POS-SICOM) Subnet*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2"
                    v-model="addTaskForm.THS_VLAN10_POS_SICOM_Subnet" hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2"
                    :message="addTaskForm.errors.THS_VLAN10_POS_SICOM_Subnet" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="THS_VLAN70 (Kiosk)*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2"
                    v-model="addTaskForm.THS_VLAN70_Kiosk_Subnet" hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2"
                    :message="addTaskForm.errors.THS_VLAN70_Kiosk_Subnet" />
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="THS_VLAN254 (Meraki-Management)*" />
                  <v-text-field variant="outlined" density="compact" required class="mt-2"
                    v-model="addTaskForm.THS_VLAN254_Meraki_Management_Subnet" hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2"
                    :message="addTaskForm.errors.THS_VLAN254_Meraki_Management_Subnet" />

                </div>
              </v-col>
              <v-col cols="6" class="pb-0">
                <div>
                  <InputLabel value="THS_VLAN4 Subnet*" />
                  <v-text-field variant="outlined" required density="compact" class="mt-2"
                    v-model="addTaskForm.THS_VLAN4_Subnet" hide-details></v-text-field>
                  <InputError v-if="addTaskForm.errors" class="mt-2" :message="addTaskForm.errors.THS_VLAN4_Subnet" />
                </div>
              </v-col>
              <v-col cols="6" class="pb-0">
                <div>
                  <InputLabel value="Assignee Name" />
                  <v-combobox variant="outlined" density="compact" clearable chips color="green "
                    v-model:search="addTask_keyword" no-filter v-model="addTask_filter_select"
                    :items="addTask_filter_items" :loading="addTask_filter_loading" item-title="name" item-value="id"
                    class="mt-2" @focus="() => addTask_filter_query(keyword)" />
                </div>
              </v-col>
              <v-col cols="6" class="pt-0">
                <div>
                  <InputLabel value="Start Date" />
                  <Datepicker variant="outlined" required density="compact"
                    style="border: 1px solid #6b7280;border-radius:5px;" class="mt-2 w-100" v-model="addTask_start_date">
                    <template v-slot:clear="{ onClear }">
                      <v-chip @click="onClear" style="color: red;left:-35px !important">x</v-chip>
                    </template>
                  </Datepicker>
                </div>
              </v-col>
              <v-col cols="6" class="pt-0">
                <div>
                  <InputLabel value="End Date" />
                  <Datepicker variant="outlined" required density="compact" class="mt-2 w-100"
                    style="border: 1px solid #6b7280;border-radius:5px;" v-model="addTask_end_date">
                    <template v-slot:clear="{ onClear }">
                      <v-chip @click="onClear" style="color: red;left:-35px !important">x</v-chip>
                    </template>
                  </Datepicker>
                </div>
              </v-col>
              <v-col cols="6">
                <div>
                  <InputLabel value="Due Date" />
                  <Datepicker variant="outlined" class="mt-2 w-100" required density="compact"
                    style="border: 1px solid #6b7280;border-radius:5px;" v-model="addTask_due_date">
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
            <v-btn size="small" variant="outlined" color="red-darken-4" text="Close" :loading="addTaskLoading"
              :disabled="addTaskLoading" @click="closeAddTaskModal"></v-btn>
            <v-btn size="small" variant="outlined" color="green-darken-3" :loading="addTaskLoading"
              :disabled="addTaskLoading" text="Submit" type="submit"></v-btn>
          </v-card-actions>
        </v-card>
      </form>
    </v-dialog>

    <template v-if="snackbar_show">
      <v-snackbar v-model="snackbar_show" :timeout="1000">
        {{ snackbar_msg }}
      </v-snackbar>
    </template>

  </AuthenticatedLayout>
</template>
<style scoped>
  .mainAssigneeCss{
    height: 32px;width: 32px;-webkit-box-align: stretch;align-items: stretch;background-color: var(--ds-surface-overlay, #FFFFFF);border-radius: 50%;box-sizing: content-box;cursor: inherit;display: flex;flex-direction: column;-webkit-box-pack: center;justify-content: center;outline: none;overflow: hidden;position: static;transform: translateZ(0px);transition: transform 200ms ease 0s, opacity 200ms ease 0s;box-shadow: 0 0 0 2px var(--ds-surface-overlay, #FFFFFF);border: none;margin: var(--ds-space-025, 2px);padding: var(--ds-space-0, 0px);font-size: inherit;font-family: inherit;
  }
  .blueBorder{
    border-width: 2px;
    border-style: solid;
    border-image: initial;
    border-color: var(--ds-icon-brand,#0052CC);
  }
  </style>