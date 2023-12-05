<script setup>
import Datepicker from 'vue3-datepicker';
import { computed, ref, watch } from 'vue';
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
console.log(JSON.stringify(importsData.value));
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
const error_msgs = ref('');

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
});

const submit = () => {
  error_msgs.value = '';
  form.post('/upload-board', {
    onSuccess: (val) =>{
      axios.get('/get-import-details', { params: { fileName: form.name,board_id: boardID.value } }).then((res) => {
        if (res.data.status == 'success') {
          console.log(JSON.stringify(res.data.data));
          var file_path = res.data.data.file_path;
          console.log(">>>>>>>>>>>>>>>>"+file_path);

          download('https://tms.hoshey.com/',file_path);
         }
      }).catch((error) => {
        // console.log(error);
      })
      // console.log(JSON.stringify(val));
      form.reset('file');
      form.file = [];
      form.name = '';
      error_msgs.value = '';
      myDialog.value = false;
      snackbar_show.value = true;
      snackbar_msg.value = "successfully uploaded";
      key_column.value = key_column.value ? false : true;
    },
    onError: (err) =>{
      error_msgs.value = err;
    },
    onFinish: () => {
      
    },
  });
};
const download = (dataurl,file_name) => {
    const url = dataurl+'/'+file_name;
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
const filter_keyword = ref('');
const filter_items = ref([]);
const filter_data = ref([]);
const filter_loading = ref(false);
const filter_scan_date = ref(new Date());

watch(filter_scan_date, (value) => {
  var date = new Date(value);
  const day = ('0' + date.getDate()).slice(-2);
  const month = date.getMonth() + 1;
  const year = date.getFullYear();
  filterForm.scan_date = year + '-' + month + '-' + day;
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

const submitBulkDelete = () => {
  
  axios.post('/delete-file', deleteForm).then((res) => {
    if (res.data.status == 'success') 
    {
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
const submitFilter = () => {
  chipModal.value = false;
  var value = filter_select.value;
  obj = {};
  if (value !== null && typeof value === 'object') {
    filterForm.assignee = value.id;
    Object.assign(obj, { Assignee: value.name });
  }

  Object.keys(filterForm).forEach(function (key) {
    console.log("<<<<<"+key);
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
  console.log(JSON.stringify(filterForm));
  closeFilterModal();
  callFilterAPI(filterForm);
};

function callFilterAPI(postData) {
  filter_data.value = postData;
  // console.log(JSON.stringify(filter_data.value));
  key_column.value = key_column.value ? false : true;
  /*axios.post('/get-cards-based-on-filters', postData).then((res) => {
    if (res.data.status == 'success') {
      columns.value = [];
      columns.value = res.data.data;
      comp_columns.value = res.data.data;
      closeFilterModal();
      nextTick();
    }
  }).catch((error) => {
    // console.log(error);
  });
  */
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
// var color_arrays = ['#DE350B','#DE350B'];

// var random = color_arrays.random();
var colors = ['#9C27B0', '#E91E63', '#673AB7', '#3F51B5', '#009688', '#795548'];

const columnReload = (obj) => {
  key_column.value = key_column.value ? false : true;
}
// const rules = (value) =>{
//     required: value => !!value || 'Field is required',
// }
</script>

<template>
  <Head>
    <title>Kanban Board</title>
  </Head>

  <AuthenticatedLayout>
    <div>
      <v-row no-gutters>
        <v-col cols="12" class="pl-3">
          <h1 class=" pt-3 font-bold text-xl">{{ boardTitle }}</h1>
        </v-col>
        <v-col cols="6" align="left" class="text-left">
          <div class="ml-6 text-lg-left left-1">
            <template v-for="boardAssignee in boardAssignees" :key="boardAssignee.id">
              <v-avatar :color="colors[Math.floor(Math.random() * colors.length)]" class="mr-1"
                style="width: 32px;height: 32px;font-size: 14px;margin-left: -10px;">
                {{ getNameInitials(boardAssignee.name) }}
              </v-avatar>
            </template>
            <v-btn class="ma-2" style="width: 32px;height: 32px;font-size: 14px;" color="indigo"
              icon="mdi mdi-account-plus-outline" @click="openAssigneeModal"></v-btn>
          </div>
        </v-col>
        <v-col cols="6" align="right" class="text-right">
          <v-btn v-if="$page.props.auth.user.roles[0].name != 'user'" size="small" color="primary"
            @click="openModal">Import</v-btn>
          <v-btn class="ml-3" v-if="$page.props.auth.user.roles[0].name != 'user'" size="small" color="red-darken-4"
            @click="openDeleteModal">Bulk Delete</v-btn>
          <v-btn style="font-size: 22px;" size="small" @click="openFilterModal" class="ma-2" variant="text"
            icon="mdi mdi-filter-variant  " color="black-lighten-4"></v-btn>
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
          <!-- <Column v-for="column in columns" :key="temp_var" :column="column" @reorder-change="onReorderChange"
            @reorder-commit="onReorderCommit" /> -->
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
                <v-select outlined label="Select" v-model="filterForm.imports"
                    :items="importsData" item-title="name"></v-select>
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
                    <v-select outlined label="Select" v-model="deleteForm.fileId"
                    :items="importsData" item-title="name"  item-value="id"></v-select>
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

    <template v-if="snackbar_show">
      <v-snackbar v-model="snackbar_show" :timeout="1000">
        {{ snackbar_msg }}
      </v-snackbar>
    </template>

  </AuthenticatedLayout>
</template>