<template>
    <Layout>
        <div
            class="min-h-screen w-full border-t border-gray-300 mt-11 bg-gray-50 flex"
        >
            <!-- Middle Section -->
            <div class="flex-1 mt-4 flex flex-col p-5 bg-gray-50">
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-l font-semibold">Orders</h1>
                </div>
                <div
                    class="flex space-x-5 border items-center rounded-md border-gray-200"
                >
                    <div class="p-2.5 flex gap-4">
                        <CreateReservation
                            @success="getterReservation"
                        ></CreateReservation>
                    </div>
            
                </div>

                <div class="my-1 gap-2">
                    <ReservationTableAll
                    ></ReservationTableAll>
                </div>
            </div>
            <Dialog v-model:visible="visible" modal :style="{ width: '45rem' }">
                <template #header>
                    <Message
                        Tag
                        icon="pi pi-user"
                        :closable="false"
                        severity="info"
                    >
                      
                    </Message>
                </template>
                <h1 class="text-1 font-bold">
                
                </h1>

               
             
                
              
            </Dialog>
        </div>
    </Layout>
</template>

<script>
import CreateReservation from "@/componentreservations/CreateReservation.vue";
import ReservationTableAll from "../component/ReservationTableAll.vue";
import Message from "primevue/message";
import Tag from "primevue/tag";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import axios from "axios";

export default {
    components: {
        Tag,
        Message,
        Button,
        CreateReservation,
        ReservationTableAll,
        Dialog,
    },
    mounted() {
        this.getterReservation();
    },
    data() {
        return {
            reservations: null,

            visible: false,
            isReservationDetails: true, 
        };
    },
    methods: {


  
        getterReservation() {
            axios.get("/get-reservations").then(({ data }) => {
                this.reservations = data;
            });
        },
      
    
  
    },
};
</script>
