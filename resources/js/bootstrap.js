import axios from "axios";
import TomSelect from "tom-select";

window.axios = axios;
window.TomSelect = TomSelect;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
