<template>
    <Button
        label="Pay Room Now"
        icon="pi pi-money-bill"
        @click="visible = true"
        class="border border-green-500 p-2 hover:bg-green-600 hover:text-white"
    />
    <Dialog
        v-model:visible="visible"
        modal
        header="Approve Reservation"
        :style="{ width: '30rem' }"
    >
        <div class="bg-gray-200 rounded-md px-4 py-2">
            <p><strong>Room Type:</strong> {{ getRoomType(reservationOrder.roomType) }}</p>
            <p><strong>Reservation Type:</strong> {{ getReservationType(reservationOrder.reservationType) }}</p>
            <p><strong>Total Price:</strong> {{ formattedPrice }}</p>
        </div>
        <Message :closable="false" severity="warn">
            Note: You cannot edit this reservation once it has been approved.
        </Message>
        <div class="flex justify-content-end gap-2">
            <Button
                type="button"
                label="Cancel"
                @click="closeDialog"
            ></Button>
            <Button
                type="button"
                label="Pay Now"
                @click="saveAndSubmit"
            ></Button>
        </div>
    </Dialog>
    <Toast />
</template>

<script>
import axios from "axios";
import Modal from "../component/Modal.vue";
import Message from "primevue/message";
import Dialog from 'primevue/dialog';
import Toast from "primevue/toast";
import Button from "primevue/button";

export default {
    props: ["idReservation"],
    data() {
        return {
            visible: false,
            reservationOrder: {},
        };
    },
    mounted() {
        this.getterReservationDetails();
    },
    components: {
        Modal,
        Dialog,
        Button,
        Toast,
        Message,
    },
    computed: {
        totalPrice() {
            const roomType = this.getRoomType(this.reservationOrder.roomType);
            const reservationType = this.getReservationType(this.reservationOrder.reservationType);

            if (roomType === 'Ordinary' && reservationType === 'Short Time') {
                return 360;
            } else if (roomType === 'Ordinary' && reservationType === 'Overnight') {
                return 650;
            } else if (roomType === 'Standard' && reservationType === 'Short Time') {
                return 430;
            } else if (roomType === 'Standard' && reservationType === 'Overnight') {
                return 860;
            }
            return 0;
        },
        formattedPrice() {
            return `₱${this.totalPrice.toFixed(2)}`;    
        }
    },

    methods: {
        getterReservationDetails() {
            axios
                .get("/get-reservations")
                .then(({ data }) => {
                    this.reservationOrder = data[0]; 
                    console.log("Reservation Order Data:", this.reservationOrder);
                })
                .catch(error => {
                    console.error("Error fetching reservation details:", error);
                });
        },

        getRoomType(typeID) {
            switch (typeID) {
                case 1:
                    return "Ordinary";
                case 2:
                    return "Standard";
                default:
                    return "Unknown";
            }
        },

        getReservationType(typeID) {
            switch (typeID) {
                case 1:
                    return "Short Time";
                case 2:
                    return "Overnight";
                default:
                    return "Unknown";
            }
        },

        saveAndSubmit() {
            this.payRoomNow();
            this.visible = false;
        },

        closeDialog() {
            this.visible = false;
        },

        payRoomNow() {
            axios
                .post("/pay-room-now", { 
                    ID: this.idReservation 
                })
                .then(() => {
                    this.$toast.add({
                        severity: "success",
                        summary: "Success!",
                        detail: "Reservation Approved Successfully!",
                        life: 3000,
                    });
                    this.$emit("Refresh");
                })
                .catch((error) => {
                    this.$toast.add({
                        severity: "error",
                        summary: "Error",
                        detail: "Failed to approve reservation. Please try again.",
                        life: 3000,
                    });
                    console.error("Error approving reservation:", error);
                });
        },
    },
};
</script>
