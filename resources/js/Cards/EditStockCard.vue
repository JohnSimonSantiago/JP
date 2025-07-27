<template>
    <div
        class="max-w-sm bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700"
    >
        <div class="flex items-center justify-center p-2">
            <img
                :src="`/uploads/${
                    equipmentNameAndImage[equipmentDetails.equipment_id]
                        ?.imageSrc
                }`"
                alt="Equipment Image"
                class="rounded-t-lg w-52 h-52"
            />
        </div>
        <div class="p-5">
            <a href="#">
                <h5
                    class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
                >
                    {{ getName(equipmentDetails.equipment_id) }}
                </h5>
            </a>
            <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">
                Current Stock:
                {{ equipmentDetails.quantity }}
            </p>
            <p class="mb-3 font-normal text-gray-700 dark:text-gray-400 flex items-center gap-1.5">
               <div class="h-5 w-5 rounded-full" 
               :class="{
    'bg-green-500': equipmentDetails.condition_id === 1,
    'bg-yellow-500': equipmentDetails.condition_id === 2,
    'bg-red-500': equipmentDetails.condition_id === 3
            }"></div>
                {{
                    equipmentDetails.condition_id === 1
                        ? "Good"
                        : equipmentDetails.condition_id === 2
                        ? "Sligthly Damaged"
                        : "Bad"
                }}
            </p>
            
        </div>
    </div>
</template>

<script>
import Toast from "primevue/toast";
import Button from "primevue/button";
import Dialog from 'primevue/dialog';

export default {
    components: {
        Toast,
        Button,
        Dialog,
    },
    data() {
        return {
            selectedCondition: "",
            equipmentNameAndImage: {},
            equipmentCondition: {},
            maxStockAll: [],
        };
    },
    mounted() {
        
        this.getEquipmentNameAndImage();
        this.getEquipmentCondition();
        this.getterMaxStockAll();
    },
    props: ["equipmentDetails"],

    methods: {
        getEquipmentCondition() {
            axios
                .get("/get-equipment-condition")
                .then(({ data }) => {
                    data.forEach((condition) => {
                        this.equipmentCondition[condition.id] = {
                            name: condition.condition,
                        };
                    });
                })
                .catch((error) => {
                    console.error("Error fetching equipment condition:", error);
                });
        },

        getEquipmentNameAndImage() {
            axios
                .get("/get-equipment-name-and-image")
                .then(({ data }) => {
                    data.forEach((equipment) => {
                        this.equipmentNameAndImage[equipment.equipment_id] = {
                            name: equipment.equipmentName,
                            imageSrc: equipment.image,
                        };
                    });
                })
                .catch((error) => {
                    console.error("Error fetching equipment data:", error);
                });
        },
        getImageSrc(equipment_id) {
            const equipment = this.equipmentNameAndImage[equipment_id];
            if (equipment) {
                return equipment.imageSrc || "Unknown";
            } else {
                return "Unknown";
            }
        },
        getName(equipment_id) {
            const equipment = this.equipmentNameAndImage[equipment_id];
            if (equipment) {
                return equipment.name || "Unknown";
            } else {
                return "Unknown";
            }
        },
        incrementQuantity() {
            this.equipmentDetails.quantity += 1;
        },
        decrementQuantity() {
            const { equipment_id, condition_id } = this.equipmentDetails;
            const maxStockItem = this.maxStockAll.find(
                (item) =>
                    item.equipment_id === equipment_id &&
                    item.condition_id === condition_id
            );

            if (maxStockItem) {
                console.log("Current Equipment ID:", equipment_id);
                console.log("Current Condition ID:", condition_id);
                console.log(
                    "Current Quantity:",
                    this.equipmentDetails.quantity
                );
                this.equipmentDetails.quantity -= 1;
            } else {
                this.$toast.add({
                    severity: "warn",
                    summary: "Warning",
                    detail: "Maximum stock data not available.",
                    life: 3000,
                });
            }
        },

        getterMaxStockAll() {
            axios.get("/get-max-stock-all").then(({ data }) => {
                this.maxStockAll = data;
                console.log(this.maxStockAll);
            });
        },
    },
};
</script>
