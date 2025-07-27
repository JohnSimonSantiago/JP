<template>
    <Button
        label="Reduce Hours"
        icon="pi pi-minus"
        @click="visible = true"
        class="border border-yellow-500 p-2 hover:bg-yellow-600 hover:text-white"
    />
    <Dialog
        v-model:visible="visible"
        modal
        header="Reduce Reservation"
        :style="{ width: '30rem' }"
    >
        <div>
            <label
                for="duration"
                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
            >
                Select Duration (Hours):
            </label>
            <div class="relative">
                <select
                    v-model="selectedDuration"
                    id="duration"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                    required
                >
                    <option value="" disabled>Select hours</option>
                    <option value="1">1 hour</option>
                    <option value="2">2 hours</option>
                    <option value="3">3 hours</option>
                    <option value="4">4 hours</option>
                    <option value="5">5 hours</option>
                </select>
            </div>
            
            <label
                for="password"
                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white mt-4"
            >
                Enter Admin Password:
            </label>
            <input
                type="password"
                v-model="password"
                id="password"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                required
            />

            <Message :closable="false" severity="error">
                Warning: You cannot undo this action
            </Message>
            <div class="flex justify-content-end gap-2 mt-4">
                <Button
                    type="button"
                    label="Cancel"
                    severity="secondary"
                    @click="visible = false"
                ></Button>
                <Button
                    type="button"
                    label="Reduce"
                    @click="saveAndSubmit"
                ></Button>
            </div>
        </div>
    </Dialog>
    <Toast />
</template>

<script>
import axios from "axios";
import Modal from "../component/Modal.vue";
import Message from "primevue/message";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import Toast from "primevue/toast";

export default {
    data() {
        return {
            visible: false,
            selectedDuration: null,
            password: '',
        };
    },
    props: ["idReservation"],
    components: {
        Modal,
        Dialog,
        Button,
        Toast,
        Message,
    },
    methods: {
        saveAndSubmit() {
            if (this.selectedDuration && this.password) {
                this.reduceReservation();
                this.visible = false;
            } else {
                this.$toast.add({
                    severity: "warn",
                    summary: "Warning",
                    detail: "Please select a duration and enter the admin password.",
                    life: 3000,
                });
            }
        },
        reduceReservation() {
            axios
                .post("/reduce-reservation", {
                    ID: this.idReservation,
                    reducedHours: this.selectedDuration,
                    adminPassword: this.password
                })
                .then(() => {
                    this.$toast.add({
                        severity: "success",
                        summary: "Success!",
                        detail: "Reservation hours reduced successfully!",
                        life: 3000,
                    });
                    this.$emit("Refresh");
                })
                .catch((error) => {
                    this.$toast.add({
                        severity: "error",
                        summary: "Error!",
                        detail: "Failed to reduce reservation: " + error.message,
                        life: 3000,
                    });
                });
        },
    },
};
</script>