<template>
    <div
        class="max-w-sm bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700"
    >
        <div class="flex items-center justify-center p-2">
            <img
                class="rounded-md w-52 h-52"
                :src="`/uploads/${equipmentDetails.image}`"
                alt=""
            />
        </div>
        <div class="px-5 py-2.5">
            <a href="#">
                <h5
                    class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
                >
                    {{ equipmentDetails.equipmentName }}
                </h5>
            </a>
            <p class="font-normal text-gray-700 dark:text-gray-400">
                {{ getCategoryName(equipmentDetails.categoryID) }}
            </p>
            <p
                class="flex space-x-2.5 text-sm items-center justify-between py-5"
            >
                <div class="flex flex-col items-center">
                    <p class="text-green-700">Good</p><Badge
                    :value="equipmentQuantities[1]"
                    size="large"
                    severity="success"
                ></Badge></div>
                 <div class="flex flex-col items-center">
                    <p class="text-yellow-500">Damaged</p><Badge
                    :value="equipmentQuantities[2]"
                    size="large"
                    severity="warning"
                ></Badge></div>
                 <div class="flex flex-col items-center">
                    <p class="text-red-500">Bad</p><Badge
                    :value="equipmentQuantities[3]"
                    size="large"
                    severity="danger"
                ></Badge></div>
            </p>

            <Button
                @click="readMore"
                label="View"
                icon="pi pi-arrow-right"
                class="bg-blue-500 px-4 py-2 text-white hover:bg-blue-600"
            >
            </Button>
        </div>
    </div>
</template>

<script>
import Button from "primevue/button";
import Badge from "primevue/badge";

export default {
    components: {
        Button,
        Badge,
    },
    data() {
        return {
            equipmentCategory: {},
            equipmentQuantities: {},
        };
    },
    mounted() {
        this.getEquipmentCategory();
        this.getEquipmentQuantities();
    },
    props: ["equipmentDetails"],
    methods: {
        async getEquipmentQuantities() {
            try {
                const quantities = {};
                for (let conditionId = 1; conditionId <= 3; conditionId++) {
                    const quantity = await this.getEquipmentQuantity(
                        conditionId
                    );
                    quantities[conditionId] = quantity;
                }
                this.equipmentQuantities = quantities;
            } catch (error) {
                console.error("Error fetching equipment quantities:", error);
            }
        },
        async getEquipmentQuantity(condition_id) {
            try {
                const params = {
                    equipment_id: this.equipmentDetails.equipment_id,
                    condition_id: condition_id,
                };

                const response = await axios.get(
                    "/get-equipment-status-quantity",
                    { params }
                );
                const quantity = response.data.quantity;
                console.log("Quantity:", quantity);
                return quantity;
            } catch (error) {
                console.error("Error fetching equipment quantity:", error);
                return 0; // Return 0 or handle the error accordingly
            }
        },

        readMore() {
            this.$emit("clicked", this.equipmentDetails);
        },
        getEquipmentCategory() {
            axios
                .get("/get-equipment-category")
                .then(({ data }) => {
                    data.forEach((category) => {
                        this.equipmentCategory[category.categoryID] = {
                            name: category.category,
                        };
                    });
                })
                .catch((error) => {
                    console.error("Error fetching equipment category:", error);
                });
        },
        getCategoryName(categoryID) {
            const category = this.equipmentCategory[categoryID];
            if (category) {
                return category.name || "Unknown";
            } else {
                return "Unknown";
            }
        },
    },
};
</script>
