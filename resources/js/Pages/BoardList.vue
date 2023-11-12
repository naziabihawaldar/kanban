<template>
    <Head>
        <title>Board List</title>
    </Head>
    <AuthenticatedLayout>
        <v-container>
            <v-row no-gutters>
                <v-col cols="8">
                    <h2 class="font-black text-xl text-gray-800 leading-tight">Board List</h2>
                </v-col>
                <v-col cols="4" align="right">
                    <v-spacer></v-spacer>
                    <v-btn size="small" color="primary" @click="openAddModal">Add Board</v-btn>
                </v-col>
                <v-col cols="12" class="mt-3">
                    <v-table>
                        <thead>
                            <tr>
                                <th class="text-left">
                                    Name
                                </th>
                                <th class="text-left">
                                    Created At
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="board in boards">
                                <td>
                                    <Link :href="`/boards/${board.id}`">{{ board.title }}</Link>
                                </td>
                                <td>{{ board.created_at }}</td>
                            </tr>
                        </tbody>
                    </v-table>
                </v-col>
            </v-row>

            <v-dialog width="500" v-model="addBoardDialog">
                <v-card>
                    <form @submit.prevent="submit">
                        <v-card title="Add Board">
                            <v-card-text>
                                <InputLabel for="title" value="Title" />
                                <TextInput id="title" type="text" class="mt-1 block w-full" style="border: 1px solid black;"
                                    v-model="form.title" required autofocus autocomplete="title" />
                                <InputError class="mt-2" :message="form.errors.title" />
                            </v-card-text>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn size="small" text="Close" @click="closeAddModal">Close</v-btn>
                                <v-btn size="small" text="Submit" type="submit">Create</v-btn>
                            </v-card-actions>
                        </v-card>
                    </form>
                </v-card>
            </v-dialog>
        </v-container>
        <v-snackbar location="top right" v-model="snackbar_show" color="#CFD8DC">
            {{ snackbar_msg }}
        </v-snackbar>

    </AuthenticatedLayout>
</template>
<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';

const addBoardDialog = ref(false);
const snackbar_show = ref(false);
const snackbar_msg = ref();

const closeAddModal = () => {
    addBoardDialog.value = false;
};
const openAddModal = () => {
    addBoardDialog.value = true;
};
const form = useForm({
    title: null,
});
const props = defineProps({
    boards: Object,
});
const submit = () => {
    form.post('/add-board', {
        onSuccess: () => closeAddModal(),
        onError: () => title.value.focus(),
        onFinish: () => {
            form.reset('title');
            addBoardDialog.value = false;
            snackbar_show.value = true;
            snackbar_msg.value = "successfully uploaded";
        },

    });
};
</script>