<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    cardID: Number,
    allcomments: Object,
    allhistory: Object,
});

const commentform = useForm({
    comment: '',
    card_id: '',
});
const card_id = ref(props?.cardID);
const comments = ref(props?.allcomments);
const snackbar_show = ref('');
const snackbar_msg = ref('');
const tab = ref('');

const addComment = () => {
    
    if (commentform.comment.length > 0) {
        axios.post('/add-comments', {
            comment: commentform.comment,
            card_id: card_id.value
        }).then((res) => {
            if (res.data.status == 'success')
            {
                commentform.reset();
                var cards_comments = res.data.data;
                comments.value = cards_comments;
            }
        }).catch((error) => {
        })
    } else {
        snackbar_show.value = true;
        snackbar_msg.value = "Please enter the comment";
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
</script>
<template>
    <div class="comments-app">
        <v-tabs v-model="tab" color="deep-purple-accent-4" align-tabs="left">
            <v-tab value="comments">Comments</v-tab>
            <v-tab value="history">History</v-tab>
        </v-tabs>
        <v-window v-model="tab">
            <v-window-item class="pt-2" value="comments">
                <form method="post" @submit.prevent="addComment">
                    <v-row>
                        <v-col cols="12" class="pb-0">
                            <v-textarea no-resize placeholder="Add Comments" variant="outlined" rows="1"
                                v-model="commentform.comment" style="width: 100%;"></v-textarea>
                        </v-col>
                        <v-col cols="12" class="pt-0" align="right">
                            <button size="small"
                                class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 pa-1 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Add
                                Comment</button>
                        </v-col>
                        
                        <v-col class="pt-0 pb-1" cols="12" v-for="(comment, index) in comments" style="border-bottom: 1px solid lightgrey;">
                            <v-row >
                                <v-col cols="1">
                                    <span style="font-size: 37px;" class="mdi mdi-account-circle"></span>
                                </v-col>
                                <v-col cols="10">
                                    <span>{{ comment.user.name }}</span><br>
                                    <span>{{ comment.comment }}</span>
                                </v-col>
                            </v-row>
                        </v-col> 
                    </v-row>

                </form>
            </v-window-item>

            <v-window-item value="history">
               <v-row class="pt-3">
                    <v-col class="" cols="12" v-for="(history, index) in allhistory" style="border-bottom: 1px solid lightgrey;">
                            <v-row class="pa-3">
                                <!-- <v-col class="pa-0"> -->
                                    <v-avatar color="blue" style="width:32px;height:32px;font-size:10px;">
                                        {{ getNameInitials(history.causer.name) }}
                                    </v-avatar>
                                <!-- </v-col> -->
                                <v-col cols="11" class="pa-0 pl-2">
                                    <span>{{ history.description }}</span>
                                </v-col>
                            </v-row>
                        </v-col> 
                </v-row> 
            </v-window-item>
        </v-window>
        <template v-if="snackbar_show">
            <v-snackbar v-model="snackbar_show" :timeout="1000">
                {{ snackbar_msg }}
            </v-snackbar>
        </template>
    </div>
</template>