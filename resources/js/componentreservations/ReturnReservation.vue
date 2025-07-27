<template>
    <Button
        label="Return Reservation"
        icon="pi pi-undo"
        @click="visible = true"
        class="border border-yellow-500 p-2 hover:bg-yellow-400 hover:text-white"
    />
    <Dialog
        v-model:visible="visible"
        modal
        header="Return Reservation"
        :style="{ width: '30rem' }"
        :closable="false"
    >
        <!-- Part 1: -->
        <div v-if="currentStep === 1">
            <div>
                <p>Is Equipment Complete?</p>
            </div>
            <div class="bg-gray-200 rounded-md px-4 py-2">
                <div v-for="order in reservationOrder" :key="order.id">
                    <OrderCard :orderDetails="order" />
                </div>
            </div>
            <div class="flex gap-2 my-2">
                <Button
                    icon="pi pi-thumbs-down-fill"
                    class="border border-red-500 p-2 hover:bg-red-600 hover:text-white"
                    label="No"
                    @click="skipToPart4"
                />
                <Button
                    icon="pi pi-thumbs-up-fill"
                    class="border border-green-500 p-2 hover:bg-green-600 hover:text-white"
                    label="Yes"
                    @click="nextStep"
                />
            </div>
        </div>

        <!-- Part 2: -->
        <div v-if="currentStep === 2">
            <div>Is Equipment in Good Condition?</div>
            <div class="flex gap-2 my-2">
                <Button
                    icon="pi pi-thumbs-down-fill"
                    class="border border-red-500 p-2 hover:bg-red-600 hover:text-white"
                    label="No"
                    @click="skipToPart5"
                />
                <Button
                    icon="pi pi-thumbs-up-fill"
                    class="border border-green-500 p-2 hover:bg-green-600 hover:text-white"
                    label="Yes"
                    @click="skipToPart3"
                />
            </div>
        </div>

        <!-- Part 3: -->
        <div v-if="currentStep === 3">
            <div>
                <label
                    for="description"
                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                    >Remarks:</label
                >
                <textarea
                    v-model="remarks"
                    id="remarks"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                    placeholder="Note"
                    rows="3"
                    required
                ></textarea>
            </div>
            <div class="flex gap-2 my-2">
                <Button
                    type="button"
                    label="Cancel"
                    @click="closeDialog"
                ></Button>
                <Button
                    type="button"
                    label="Confirm"
                    @click="SubmitReturn"
                ></Button>
            </div>
        </div>

        <!-- Part 4: -->
        <div v-if="currentStep === 4">
            <p>Input Missing Equipment</p>
            <div class="bg-gray-200 rounded-md px-4 py-2 my-2">
                <div v-for="order in reservationOrder" :key="order.id">
                    <InputMissingEquipmentCard :orderDetails="order" />
                </div>
            </div>
            <div>
                <label
                    for="description"
                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                    >Remarks:</label
                >
                <textarea
                    v-model="remarks"
                    id="remarks"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                    placeholder="Note"
                    rows="3"
                    required
                ></textarea>
            </div>
            <div class="flex gap-2 my-2">
                <Button
                    icon="pi pi-thumbs-down-fill"
                    class="border border-red-500 p-2 hover:bg-red-600 hover:text-white"
                    label="Cancel"
                    @click="closeDialog"
                />
                <Button
                    icon="pi pi-thumbs-up-fill"
                    class="border border-green-500 p-2 hover:bg-green-600 hover:text-white"
                    label="Confirm"
                    @click="submitReplacementDetails"
                />
            </div>
        </div>

        <!-- Part 5: -->
        <div v-if="currentStep === 5">
            <p>This is Part 5 .</p>
        </div>
    </Dialog>
    <Toast />
</template>

<script>
import axios from "axios";
import Modal from "../component/Modal.vue";
import Button from "primevue/button";
import Toast from "primevue/toast";
import Dialog from "primevue/dialog";
import OrderCard from "../Card_small/OrderCard.vue";
import InputMissingEquipmentCard from "../Card_small/InputMissingEquipmentCard.vue";

export default {
    props: ["idReservation"],
    data() {
        return {
            visible: false,
            currentStep: 1,
            reservationOrder: [],
            originalReservationOrder: [],
            remarks: "",
            modalContent: {
                title: "Return Reservation",
                content: "",
            },
        };
    },
    components: {
        Toast,
        OrderCard,
        InputMissingEquipmentCard,
        Dialog,
        Modal,
        Button,
    },
    mounted() {
        this.getterReservationOrder();
    },
    methods: {
        getterReservationOrder() {
            axios
                .get("/get-reservation-orders", {
                    params: {
                        reservationNumber: this.idReservation,
                    },
                })
                .then(({ data }) => {
                    this.reservationOrder = data;
                    // Deep clone to preserve original state
                    this.originalReservationOrder = JSON.parse(
                        JSON.stringify(data)
                    );
                    console.log(this.reservationOrder);
                });
        },
        nextStep() {
            this.currentStep++;
        },
        skipToPart3() {
            this.currentStep = 3;
        },
        skipToPart4() {
            this.currentStep = 4;
        },
        skipToPart5() {
            this.currentStep = 5;
        },
        closeDialog() {
            this.visible = false;
            this.currentStep = 1;
            this.resetReservationOrder();
        },
        returnReservationIncomplete() {
            axios
                .post("/return-reservation-incomplete", {
                    ID: this.idReservation,
                })
                .then(() => {
                    this.$toast.add({
                        severity: "success",
                        summary: "Success!",
                        detail: "Reservation Returned Incompletely!",
                        life: 3000,
                    });
                    this.$emit("Refresh");
                });
        },
        submitReplacementDetails() {
            const replacementDetails = this.reservationOrder.map((order) => ({
                reservationNumber: this.idReservation,
                equipment_id: order.equipment_id,
                quantity: order.quantity,
            }));

            axios
                .post("/submit-replacement-details", replacementDetails)
                .then(() => {
                    this.$toast.add({
                        severity: "success",
                        summary: "Success!",
                        detail: "Replacement details submitted successfully!",
                        life: 3000,
                    });
                    this.$emit("Refresh");
                })
                .catch((error) => {
                    console.error(
                        "Error submitting replacement details:",
                        error
                    );
                    this.$toast.add({
                        severity: "error",
                        summary: "Error",
                        detail: "Failed to submit replacement details.",
                        life: 3000,
                    });
                });
        },
        returnReservation() {
            axios
                .post("/return-reservation", {
                    ID: this.idReservation,
                    remarks: this.remarks, // Include the remarks in the request
                })
                .then(() => {
                    this.$toast.add({
                        severity: "success",
                        summary: "Success!",
                        detail: "Reservation Returned Successfully!",
                        life: 3000,
                    });
                    this.$emit("Refresh");
                })
                .catch((error) => {
                    console.error("Error returning reservation:", error);
                    this.$toast.add({
                        severity: "error",
                        summary: "Error",
                        detail: "Failed to return reservation.",
                        life: 3000,
                    });
                });
        },
        SubmitReturn() {
            this.returnReservation();
            this.resetReservationOrder();
            this.closeDialog();
        },
        resetReservationOrder() {
            // Reset the reservationOrder to its original state
            this.reservationOrder = JSON.parse(
                JSON.stringify(this.originalReservationOrder)
            );
        },
    },
};
</script>
