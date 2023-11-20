<script setup>
import Datepicker from 'vue3-datepicker';
import { computed, nextTick, ref, reactive, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { PencilIcon } from '@heroicons/vue/24/solid';
import { TrashIcon } from '@heroicons/vue/24/solid';
import { useEditCard } from '@/Composables/useEditCard';
import ConfirmDialog from '@/Components/Kanban/ConfirmDialog.vue';
import Comment from '@/Components/Kanban/Comment.vue';
const props = defineProps({
  card: Object,
});
// console.log(props?.card?.users);
// TODO: Move to composable useModal
const isOpen = ref(false);
const isDialogOpen = ref(false);
const panel = ref([0]);


const closeModal = confirm => {
  isOpen.value = false;
  if (confirm) {
    router.delete(
      route('columns.cards.destroy', {
        card: props?.card?.id,
        column: props?.card?.column,
      })
    );
  }
};
const openModal = () => (isOpen.value = true);

const form = useForm({
  content: props?.card?.content,
});

const inputCardContentRef = ref();
const isEditing = computed(
  () => props?.card?.id === useEditCard?.value?.currentCard
);

const cardContent = computed(() => props.card?.content);
const assign_users = computed(() => props.card?.users);
const comments = computed(() => props.card?.comments);

const card_id = computed(() => props.card?.id);
const onSubmit = () => {
  form.post(
    route('columns.cards.update', {
      column: props?.card?.column,
      card: props?.card?.id,
    }),
    {
      onSuccess: () => {
        useEditCard.value.currentCard = null;
      },
    }
  );
};

const onCancel = () => {
  useEditCard.value.currentCard = null;
  form.reset();
};

const showForm = async () => {
  useEditCard.value.currentCard = props?.card?.id;
  await nextTick(); // wait for form to be rendered
  inputCardContentRef.value.focus();
};

//Details
const openDetailModal = () => (isDialogOpen.value = true);
const closeDetailModal = () => (isDialogOpen.value = false);

// const openDetailModal = () => {
//   console.log("clicked");
//   axios.post('/get-card-details', {card_id:card_id.value}).then((res) => {
//     if(res.data.status == 'success')
//     {
//       isDialogOpen.value = true;
//       // console.log(JSON.stringify(res.data.data));
     
//     }
//   }).catch((error) => {
//     // console.log(error);
//   })
//   // console.log(JSON.stringify());
// };


//Autocomplete
const data = reactive({
  posts: [],
  term: ''
});

//Demo
const select = ref(null);
const keyword = ref('');
const items = ref([]);
const loading = ref(false);
const start_date = ref((props?.card?.start_date != null) ? new Date(props?.card?.start_date):null);
const end_date = ref((props?.card?.end_date != null) ? new Date(props?.card?.end_date): null );
const due_date = ref((props?.card?.due_date != null) ? new Date(props?.card?.due_date): null );

// 
select.value = assign_users.value[0];
const query = async (_keyword) => {
  if (_keyword != null && _keyword.length > 1) {
    loading.value = true;
    const response = await axios.post('/search-user', {
      query: _keyword
    });
    if (response.data.length) {
      items.value = response.data;
      loading.value = false;
    } else {
      data.message = 'Nothing found!';
    }
  }

};

watch(select, (value) => {
  if (value !== null && typeof value === 'object') {
    axios.post('/assign-card', {
      user_id: value.id,
      card_id: card_id.value
    }).then((res) => {
      const response = res;
      router.reload();
    }).catch((error) => {
      console.log(error);
      console.log("error occurred");
    })

  }
});

watch(keyword, (v) => {
  query(v);
});
var getInitials = function (string) {
  var names = string.split(' '),
    initials = names[0].substring(0, 1).toUpperCase();

  if (names.length > 1) {
    initials += names[names.length - 1].substring(0, 1).toUpperCase();
  }
  return initials;
};
watch(start_date, (value) => {
  var date = new Date(value);
  const day = ('0' + date.getDate()).slice(-2);
  const month = date.getMonth() + 1;
  const year = date.getFullYear();
  var actual_start_date = year+'-'+month+'-'+day;
  axios.post('/update-card-details', {type:'start_date',date:actual_start_date,card_id:card_id.value}).then((res) => {
    if(res.data.status == 'success')
    {
           
    }
  }).catch((error) => {
    // console.log(error);
  })
});
watch(end_date, (value) => {
  var date = new Date(value);
  const day = ('0' + date.getDate()).slice(-2);
  const month = date.getMonth() + 1;
  const year = date.getFullYear();
  var actual_end_date = year+'-'+month+'-'+day;
  axios.post('/update-card-details', {type:'end_date',date:actual_end_date,card_id:card_id.value}).then((res) => {
    if(res.data.status == 'success'){ }
  }).catch((error) => {
    // console.log(error);
  })
});
watch(due_date, (value) => {
  var date = new Date(value);
  const day = ('0' + date.getDate()).slice(-2);
  const month = date.getMonth() + 1;
  const year = date.getFullYear();
  var actual_due_date = year+'-'+month+'-'+day;
  axios.post('/update-card-details', {type:'due_date',date:actual_due_date,card_id:card_id.value}).then((res) => {
    if(res.data.status == 'success'){ }
  }).catch((error) => {
    // console.log(error);
  })
});
</script>

<template>
  <div @click="openDetailModal"
    class="cursor-move relative group p-2 bg-white shadow rounded-md border border-b border-gray-300 hover:bg-gray-50">
    <form v-if="isEditing" @keydown.esc="onCancel" @submit.prevent="onSubmit">
      <textarea v-model="form.content" type="text" @keydown.enter.prevent="onSubmit" placeholder="Card content ..."
        ref="inputCardContentRef" rows="3"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
      </textarea>
      <div class="mt-2 space-x-2">
        <button type="submit"
          class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
          Save card
        </button>
        <button @click.prevent="onCancel" type="button"
          class="inline-flex items-center rounded-md border border-transparent px-4 py-2 text-sm font-medium text-gray-700 hover:text-black focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
          Cancel
        </button>
      </div>
    </form>
    <div v-else>
      <p class="text-sm">{{ cardContent }}</p>
      <div class="mt-2 text-lg-right right-1">
        <template v-for="user in assign_users" :key="user.id">
          <v-avatar color="blue" style="width:22px;height:22px;font-size:10px;">
            {{ getInitials(user.name) }}
          </v-avatar>
        </template>

      </div>
      <div class="hidden absolute right-1 inset-0 group-hover:flex justify-end space-x-2 items-center">
        <button @click.prevent="showForm"
          class="w-8 h-8 bg-gray-50 text-gray-600 hover:text-black hover:bg-gray-200 rounded-md grid place-content-center">
          <PencilIcon class="w-5 h-5" />
        </button>
        <button @click.prevent="openModal"
          class="w-8 h-8 bg-gray-50 text-red-600 hover:text-red-700 hover:bg-gray-200 rounded-md grid place-content-center">
          <TrashIcon class="w-5 h-5" />
        </button>
      </div>
    </div>
  </div>

  <ConfirmDialog :show="isOpen" @confirm="closeModal($event)" title="Remove Card"
    message="Are you sure you want to delete this card?" />

  <v-dialog v-model="isDialogOpen" width="1200" height="800">
    <v-card class="h-full">
      <v-card-title>
        {{ cardContent }}
      </v-card-title>
      <v-card-text>
        <v-row>
          <v-col cols="8" class="overflow-x-auto" style="height: 570px;">
            <v-row>
              <v-col cols="6">
                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Vulnerability Name :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.content }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Vulnerability ID :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.vulnerability_id }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Vulnerability Description :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.vulnerability_desc }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Vulnerability Details :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.vulnerability_details }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Asset IP :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.asset_ip }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">IP & Vuln Id :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.ip_and_vuln_id }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Port :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.port }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Protocol :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.protocol }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">OS Type/Version :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.os_type }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">OS Version :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.os_version }}</v-col>
                </v-row>
                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Business Unit :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.business_unit }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Class :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.class }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">CVE ID (Common Vulnerabilities and Exposures ID)
                      :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.cve_id }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">CVSS Score (Common Vulnerability Scoring System
                      Score) :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.cvss_score }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Severity :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.severity }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Solution :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.solution }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Impact Of Vulnerability :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.impact_of_vulnerability }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Scan Date Time :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.scan_date_time }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Background :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.background }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Service :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.service }}</v-col>
                </v-row>
              </v-col>
              <v-col cols="6">
                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Remediation :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.remediation }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">References :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.references }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Exception :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.exception }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Tags :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.tags }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Asset Version :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.asset_version }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Model :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.model }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Make :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.make }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Asset Type :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.asset_type }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Host Name :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.host_name }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">PLK_VLAN10 (POS-SICOM) Subnet :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.PLK_VLAN10_POS_SICOM_Subnet }}</v-col>
                </v-row>
                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">PLK_VLAN70 (Kiosk) :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.PLK_VLAN70_Kiosk_Subnet }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">PLK_VLAN254 (Meraki-Management) :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.PLK_VLAN254_Meraki_Management_Subnet }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">PLK_VLAN4 Subnet :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.PLK_VLAN4_Subnet }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">BK_VLAN10 (POS-SICOM) Subnet :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.BK_VLAN10_POS_SICOM_Subnet }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">BK_VLAN70 (Kiosk) :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.BK_VLAN70_Kiosk_Subnet }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">BK_VLAN254 (Meraki-Management) :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.BK_VLAN254_Meraki_Management_Subnet }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">BK_VLAN4 Subnet :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.BK_VLAN4_Subnet }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">THS_VLAN10 (POS-SICOM) Subnet :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.THS_VLAN10_POS_SICOM_Subnet }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">THS_VLAN70 (Kiosk) :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.THS_VLAN70_Kiosk_Subnet }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">THS_VLAN254 (Meraki-Management) :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.THS_VLAN254_Meraki_Management_Subnet }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">THS_VLAN4 Subnet :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.THS_VLAN4_Subnet }}</v-col>
                </v-row>

              </v-col>
              <v-col cols="12">
                <Comment  :cardID = card_id :allcomments="comments"/>
              </v-col> 
            </v-row>
          </v-col>
          <v-col cols="4">
            <v-expansion-panels v-model="panel">
              <v-expansion-panel class="pa-0">
                <v-expansion-panel-title class="pa-2"
                  style="min-height: 35px; !important;border:1px solid #DFE1E6; background:#ffffff">Details</v-expansion-panel-title>
                <v-expansion-panel-text class="pa-0">
                  <v-row>
                    <v-col cols="3" class="pl-0 pt-3 pb-3">
                      <b class="accent--text" style="font-size:14px">Assignee</b>
                    </v-col>
                    <v-col cols="9" class="pl-0 pt-3 pb-3">

                      <v-combobox outlined clearable chips color="green " v-model:search="keyword" no-filter
                        v-model="select" :items="items" :loading="loading" item-title="name" item-value="id" return-object
                        @focus="() => query(keyword)" />
                    </v-col>
                    <v-col cols="3" class="pl-0 pt-3 pb-3">
                      <b class="accent--text" style="font-size:14px">Start Date</b>
                    </v-col>
                    <v-col cols="9" class="pl-0 pt-3 pb-3">
                      <Datepicker style="border: 1px solid lightgrey;" :clearable="true"
                        v-model="start_date">
                        <template v-slot:clear="{ onClear }">
                          <v-chip @click="onClear" style="color: red;left:-35px !important;">x</v-chip>
                        </template>
                      </Datepicker>
                    </v-col>
                    <v-col cols="3" class="pl-0 pt-3 pb-3">
                      <b class="accent--text" style="font-size:14px">End Date</b>
                    </v-col>
                    <v-col cols="9" class="pl-0 pt-3 pb-3">
                      <Datepicker style="border: 1px solid lightgrey;" :clearable="true"
                        v-model="end_date">
                        <template v-slot:clear="{ onClear }">
                          <v-chip @click="onClear" style="color: red;left:-35px !important">x</v-chip>
                        </template>
                      </Datepicker>
                    </v-col>
                    <v-col cols="3" class="pl-0 pt-3 pb-3">
                      <b class="accent--text" style="font-size:14px">Due Date</b>
                    </v-col>
                    <v-col cols="9" class="pl-0 pt-3 pb-3">
                      <Datepicker style="border: 1px solid lightgrey;" :clearable="true"
                        v-model="due_date">
                        <template v-slot:clear="{ onClear }">
                          <v-chip @click="onClear" style="color: red;left:-35px !important">x</v-chip>
                        </template>
                      </Datepicker>
                    </v-col>
                  </v-row>
                </v-expansion-panel-text>
              </v-expansion-panel>
            </v-expansion-panels>
          </v-col>
        </v-row>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="primary" size="small" @click="closeDetailModal">Close</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
