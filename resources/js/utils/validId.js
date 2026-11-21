// Valid IDs are private files (no public URL). Admin pages load one through the
// admin-only route with the login token and show it as a temporary in-memory image.
// Always release it with URL.revokeObjectURL(url) when the viewer closes.
import axios from "axios";

export async function loadValidIdUrl(userId) {
    const res = await axios.get(`/api/admin/users/${userId}/valid-id`, {
        responseType: "blob",
        headers: { Authorization: `Bearer ${localStorage.getItem("auth-token")}` },
    });
    return URL.createObjectURL(res.data);
}
