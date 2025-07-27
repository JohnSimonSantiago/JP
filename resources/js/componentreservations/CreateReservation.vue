<template>
    <Button
        label="Create Reservation"
        icon="pi pi-file-edit"
        @click="visible = true"
        class="border border-green-500 p-2 hover:bg-green-600 hover:text-white"
    />
    <Dialog
        v-model:visible="visible"
        modal
        header="Create Reservation"
        :style="{ width: '40rem' }"
    >
        <form class="">
            <div class="gap-6 mb-6 flex items-center justify-center">
                <div>
                    <!-- Customer Name -->
                    <div>
                        <label
                            for="customer_name"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                        >
                            Customer Name
                        </label>
                        <input
                            v-model="customerName"
                            type="text"
                            id="customer_name"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            placeholder="Customer Name"
                            required
                        />
                    </div>

                    <!-- Address -->
                    <div>
                        <label
                            for="address"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                        >
                            Address
                        </label>
                        <input
                            v-model="address"
                            type="text"
                            id="address"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            placeholder="Enter Address"
                            required
                        />
                    </div>

                    <!-- Room Number -->
                    <div>
                        <label
                            for="roomNumber"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                        >
                            Room Number
                        </label>

                        <select
                            v-model="roomNumber"
                            id="roomNumber"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            required
                        >
                            <option v-for="number in 25" :key="number" :value="number">
                                {{ number }}
                            </option>
                        </select>
                    </div>

                    <!-- Reservation Type -->
                    <div>
                        <label
                            for="reservationType"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                        >
                            Reservation Type
                        </label>

                        <select
                            v-model="reservationType"
                            id="reservationType"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            required
                        >
                            <option value="Short Time">Short Time</option>
                            <option value="Overnight">Overnight</option>
                        </select>
                    </div>

                    <!-- Room Type -->
                    <div>
                        <label
                            for="roomType"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                        >
                            Room Type
                        </label>

                        <select
                            v-model="roomType"
                            id="roomType"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            required
                        >
                            <option value="Ordinary">Ordinary</option>
                            <option value="Standard">Standard</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Price Display -->
            <div class="mb-6 text-lg font-medium">
                Total Price: {{ formattedPrice }}
            </div>

            <div class="flex justify-content-end gap-2">
                <Button
                    type="button"
                    label="Cancel"
                    severity="secondary"
                    @click="visible = false"
                ></Button>
                <Button
                    type="button"
                    label="Submit"
                    @click="saveAndSubmit"
                ></Button>
            </div>
        </form>
    </Dialog>
    <Toast />
</template>
<script>
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Button from 'primevue/button';
import axios from 'axios';

export default {
    components: {
        Dialog,
        Button,
        Toast,
    },
    data() {
        return {
            visible: false,
            customerName: null,
            address: null,
            roomNumber: null,
            reservationType: null,
            roomType: null,
        };
    },

    computed: {
        // Computed property to calculate the price based on the selected room and reservation type
        totalPrice() {
            if (this.roomType === 'Ordinary' && this.reservationType === 'Short Time') {
                return 360;
            } else if (this.roomType === 'Ordinary' && this.reservationType === 'Overnight') {
                return 650;
            } else if (this.roomType === 'Standard' && this.reservationType === 'Short Time') {
                return 430;
            } else if (this.roomType === 'Standard' && this.reservationType === 'Overnight') {
                return 860;
            }
            return 0;
        },
        formattedPrice() {
            return `₱${this.totalPrice.toFixed(2)}`; // Display price with currency format
        }
    },

    methods: {
        saveAndSubmit() {
            this.submitReservation();
            this.visible = false;
        },
        submitReservation() {
            const { customerName, address, roomNumber } = this;

            // Convert reservationType and roomType to numeric values
            const reservationTypeValue = this.reservationType === 'Short Time' ? 1 : 2;
            const roomTypeValue = this.roomType === 'Ordinary' ? 1 : 2;

            axios
                .post("/submit-reservation", {
                    customerName,
                    address,
                    roomNumber,
                    reservationType: reservationTypeValue,
                    roomType: roomTypeValue,
                })
                .then(({ data }) => {
                    this.$toast.add({
                        severity: "success",
                        summary: "Success!",
                        detail: "Reservation Created Successfully!",
                        life: 3000,
                    });
                    this.$emit("success");
                })
                .catch((error) => {
                    this.$toast.add({
                        severity: "error",
                        summary: "Error",
                        detail: "Failed to create reservation",
                        life: 3000,
                    });
                    console.error("Error submitting reservation:", error);
                });
        },
    },
};
</script>
