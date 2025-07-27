<template>
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table
            class="w-full text-sm text-left rtl:text-right dark:text-gray-400"
        >
            <thead>
                <tr
                    class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-gray-700 dark:text-gray-400"
                >
                    <th scope="col" class="text-center px-6 py-3">
                        Reservation Number
                    </th>
                    <th scope="col" class="text-center">Customer Name</th>
                    <th scope="col" class="text-center">Status</th>
                    <th scope="col" class="text-center">Time In</th>
                    <th scope="col" class="text-center">Time Out</th>
                    <th scope="col" class="text-center">Reservation Type</th>
                    <th scope="col" class="text-center">Room</th>
                    <th scope="col" class="text-center">Additional Hours</th>
                    <th scope="col" class="text-center">Total Amount</th>
                    <th scope="col" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="">
                <tr v-for="(reservation, index) in reservations">
                    <td class="text-center text-base">
                        No. {{ reservation.reservationNumber }}
                    </td>
                    <td class="text-center text-base">
                        {{ reservation.customerName }}
                    </td>
                    <td class="text-center">
                        <div class="flex justify-center">
                            <Message
                                :closable="false"
                                :severity="
                                    getStatusSeverity(reservation.statusID)
                                "
                            >
                                {{ getStatus(reservation.statusID) }}
                            </Message>
                        </div>
                    </td>
                    <td class="text-center text-base">
                        {{ formatDate(reservation.created_at) }}
                    </td>
                    <td class="text-center text-base">
                        {{ reservation.timeOut }}
                    </td>
                    <td class="text-center text-base">
                        {{ reservation.reservationType }}
                    </td>
                    <td class="text-center text-base">
                        {{ reservation.roomType }}
                    </td>
                    <td class="text-center text-base">
                        {{ formatAdditionalHours(reservation.additionalHours) }}
                    </td>
                    <td class="text-center text-base">
                        PHP {{ calculateTotalAmount(reservation) }}
                    </td>
                    <td class="text-center">
                        <div class="flex flex-col justify-center gap-2 p-2">
                            <PayRoomNow
                                v-if="reservation.statusID === 1"
                                :idReservation="reservation.reservationNumber"
                                @Refresh="getterReservations"
                            ></PayRoomNow>
                            <Finish
                                v-if="reservation.statusID === 2"
                                :idReservation="reservation.reservationNumber"
                                @Refresh="getterReservations"
                            ></Finish>
                            <AdditionalHours
                                v-if="
                                    reservation.statusID === 1 ||
                                    reservation.statusID === 2
                                "
                                :idReservation="reservation.reservationNumber"
                                @Refresh="getterReservations"
                            ></AdditionalHours>
                            <ReduceHours
                                v-if="
                                    reservation.statusID === 1 ||
                                    reservation.statusID === 2
                                "
                                :idReservation="reservation.reservationNumber"
                                @Refresh="getterReservations"
                            ></ReduceHours>
                            <Button
                                v-if="
                                    reservation.statusID >= 1 &&
                                    reservation.statusID <= 5
                                "
                                @click="readMore(reservation)"
                                label="View"
                                icon="pi pi-arrow-right"
                                class="border border-blue-500 p-2 hover:bg-blue-400 hover:text-white"
                            >
                            </Button>
                            <Button
                                v-if="reservation.statusID === 6"
                                @click="viewReplacementDetails(reservation)"
                                label="View Replacements"
                                icon="pi pi-arrow-right"
                                class="border border-blue-500 p-2 hover:bg-blue-400 hover:text-white"
                            >
                            </Button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
// import axios from "axios";
// import Modal from "../component/Modal.vue";
// import Button from "primevue/button";
// import AdditionalHours from "../componentReservations/AdditionalHours.vue";
// import ReduceHours from "../componentReservations/ReduceHours.vue";
// import Finish from "../componentReservations/Finish.vue";
// import Message from "primevue/message";
// import PayRoomNow from "../componentReservations/PayRoomNow.vue";

export default {
    components: {
        // Modal,
        // Button,
        // Message,
        // Finish,
        // AdditionalHours,
        // ReduceHours,
        // PayRoomNow,
    },
    mounted() {
        this.getterReservations();
        this.getStatusTable();
    },
    data() {
        return {
            reservations: [],
            replacementDetails: [],
            statusTable: {},
        };
    },
    props: ["reservationDetails"],
    methods: {
        getStatusSeverity(statusID) {
            switch (statusID) {
                case 1:
                    return "warn";
                case 2:
                    return "success";
                case 3:
                    return "info";
                case 4:
                    return "success";
                case 5:
                    return "error";
                case 6:
                    return "warn";
                default:
                    return "info";
            }
        },
        getStatusTable() {
            axios.get("/get-status-table").then(({ data }) => {
                data.forEach((status) => {
                    this.statusTable[status.id] = status.status;
                });
            });
        },
        getStatus(statusID) {
            return this.statusTable[statusID] || "Unknown";
        },
        getterReservations() {
            axios.get("/get-reservations").then(({ data }) => {
                this.reservations = data;
            });
        },
        readMore(reservation) {
            console.log(reservation);
            this.$emit("clicked", reservation);
            this.$emit("reservationSelected", reservation.reservationNumber);
        },
        formatDate(dateString) {
            const date = new Date(dateString);

            // Format the date without the year
            const formattedDate = date.toLocaleDateString("en-US", {
                month: "long",
                day: "numeric",
            });

            // Format the time
            const formattedTime = date.toLocaleTimeString("en-US", {
                hour: "numeric",
                minute: "numeric",
                hour12: true, // Change to false for 24-hour format
            });

            return `${formattedDate} at ${formattedTime}`;
        },
        formatAdditionalHours(hours) {
            if (hours === null || hours === 0) {
                return "No additional hours";
            }
            const cost = hours * 150; // 150 PHP per hour
            const hourText = hours === 1 ? "Hour" : "Hours";
            return `${hours} ${hourText} (PHP ${cost})`;
        },
        calculateTotalAmount(reservation) {
            let baseAmount = 0;
            if (
                reservation.roomType === "Ordinary" &&
                reservation.reservationType === "Short Time"
            ) {
                baseAmount = 360;
            } else if (
                reservation.roomType === "Ordinary" &&
                reservation.reservationType === "Overnight"
            ) {
                baseAmount = 650;
            } else if (
                reservation.roomType === "Standard" &&
                reservation.reservationType === "Short Time"
            ) {
                baseAmount = 430;
            } else if (
                reservation.roomType === "Standard" &&
                reservation.reservationType === "Overnight"
            ) {
                baseAmount = 860;
            }

            const additionalHoursCost =
                (reservation.additionalHours || 0) * 150;
            return baseAmount + additionalHoursCost;
        },
    },
};
</script>
