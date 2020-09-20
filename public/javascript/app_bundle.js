/******/ (function(modules) { // webpackBootstrap
/******/ 	// install a JSONP callback for chunk loading
/******/ 	function webpackJsonpCallback(data) {
/******/ 		var chunkIds = data[0];
/******/ 		var moreModules = data[1];
/******/ 		var executeModules = data[2];
/******/
/******/ 		// add "moreModules" to the modules object,
/******/ 		// then flag all "chunkIds" as loaded and fire callback
/******/ 		var moduleId, chunkId, i = 0, resolves = [];
/******/ 		for(;i < chunkIds.length; i++) {
/******/ 			chunkId = chunkIds[i];
/******/ 			if(Object.prototype.hasOwnProperty.call(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 				resolves.push(installedChunks[chunkId][0]);
/******/ 			}
/******/ 			installedChunks[chunkId] = 0;
/******/ 		}
/******/ 		for(moduleId in moreModules) {
/******/ 			if(Object.prototype.hasOwnProperty.call(moreModules, moduleId)) {
/******/ 				modules[moduleId] = moreModules[moduleId];
/******/ 			}
/******/ 		}
/******/ 		if(parentJsonpFunction) parentJsonpFunction(data);
/******/
/******/ 		while(resolves.length) {
/******/ 			resolves.shift()();
/******/ 		}
/******/
/******/ 		// add entry modules from loaded chunk to deferred list
/******/ 		deferredModules.push.apply(deferredModules, executeModules || []);
/******/
/******/ 		// run deferred modules when all chunks ready
/******/ 		return checkDeferredModules();
/******/ 	};
/******/ 	function checkDeferredModules() {
/******/ 		var result;
/******/ 		for(var i = 0; i < deferredModules.length; i++) {
/******/ 			var deferredModule = deferredModules[i];
/******/ 			var fulfilled = true;
/******/ 			for(var j = 1; j < deferredModule.length; j++) {
/******/ 				var depId = deferredModule[j];
/******/ 				if(installedChunks[depId] !== 0) fulfilled = false;
/******/ 			}
/******/ 			if(fulfilled) {
/******/ 				deferredModules.splice(i--, 1);
/******/ 				result = __webpack_require__(__webpack_require__.s = deferredModule[0]);
/******/ 			}
/******/ 		}
/******/
/******/ 		return result;
/******/ 	}
/******/
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// object to store loaded and loading chunks
/******/ 	// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 	// Promise = chunk loading, 0 = chunk loaded
/******/ 	var installedChunks = {
/******/ 		"app": 0
/******/ 	};
/******/
/******/ 	var deferredModules = [];
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/ 	var jsonpArray = window["webpackJsonp"] = window["webpackJsonp"] || [];
/******/ 	var oldJsonpFunction = jsonpArray.push.bind(jsonpArray);
/******/ 	jsonpArray.push = webpackJsonpCallback;
/******/ 	jsonpArray = jsonpArray.slice();
/******/ 	for(var i = 0; i < jsonpArray.length; i++) webpackJsonpCallback(jsonpArray[i]);
/******/ 	var parentJsonpFunction = oldJsonpFunction;
/******/
/******/
/******/ 	// add entry module to deferred list
/******/ 	deferredModules.push(["./assets/javascript/App.jsx","vendor"]);
/******/ 	// run deferred modules when ready
/******/ 	return checkDeferredModules();
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/javascript/App.jsx":
/*!***********************************!*\
  !*** ./assets/javascript/App.jsx ***!
  \***********************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react_dom__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react-dom */ "./node_modules/react-dom/index.js");
/* harmony import */ var react_dom__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(react_dom__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _Search_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Search.jsx */ "./assets/javascript/Search.jsx");
/* harmony import */ var _Colophon_jsx__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./Colophon.jsx */ "./assets/javascript/Colophon.jsx");
/* harmony import */ var _styles_app_scss__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../styles/app.scss */ "./assets/styles/app.scss");
/* harmony import */ var _styles_app_scss__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_styles_app_scss__WEBPACK_IMPORTED_MODULE_4__);





react_dom__WEBPACK_IMPORTED_MODULE_1___default.a.render( /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Search_jsx__WEBPACK_IMPORTED_MODULE_2__["default"]), document.getElementById('search'));
react_dom__WEBPACK_IMPORTED_MODULE_1___default.a.render( /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Colophon_jsx__WEBPACK_IMPORTED_MODULE_3__["default"]), document.getElementById('colophon')); // const element = (
//     <>
//         <Router>
//             <Page />
//         </Router>
//     </>
// );
// ReactDOM.render(element, document.getElementById('root'));
// Hot Module Replacement

if (false) {}

/***/ }),

/***/ "./assets/javascript/Colophon.jsx":
/*!****************************************!*\
  !*** ./assets/javascript/Colophon.jsx ***!
  \****************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Colophon; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _storageAvailable_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./storageAvailable.js */ "./assets/javascript/storageAvailable.js");


function Colophon() {
  if (!Object(_storageAvailable_js__WEBPACK_IMPORTED_MODULE_1__["default"])('localStorage')) {
    return '';
  }

  localStorage.clear();
  const [open, setOpen] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState(true);

  function handleClose(event) {
    event.preventDefault();
    setOpen(false);
    localStorage.setItem('colophon', 'closed');
  }

  if (open && localStorage.getItem('colophon') !== 'closed') {
    return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
      className: "container",
      style: {
        position: 'fixed',
        zIndex: 999,
        right: 0,
        bottom: 0,
        left: 0,
        backgroundColor: 'black',
        fontSize: '15px',
        color: '#BBB',
        boxShadow: '0 0 10px -5px black'
      }
    }, "Welcome to Videogam.in, a site about videogames. ", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("b", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
      href: "/about.php"
    }, "Read more")), " about this site or else ", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
      href: "#close",
      title: "hide this message and don't show it to me again",
      className: "tooltip",
      onClick: handleClose
    }, "pay me for the door repair charge"), ".");
  }

  return '';
}

/***/ }),

/***/ "./assets/javascript/Search.jsx":
/*!**************************************!*\
  !*** ./assets/javascript/Search.jsx ***!
  \**************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Search; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);

const API_ENDPOINT = '/api/search?q=';
function Search() {
  const [searchTerm, setSearchTerm] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState('');
  const resultsInitialState = {
    hits: [],
    isLoading: false,
    isError: false
  };

  const resultsReducer = (state, action) => {
    switch (action.type) {
      case 'SEARCH_FETCH_INIT':
        return { ...state,
          isLoading: true,
          isError: false
        };

      case 'SEARCH_FETCH_SUCCESS':
        return { ...state,
          isLoading: false,
          isError: false,
          hits: action.payload
        };

      case 'SEARCH_FETCH_FAIL':
        return { ...state,
          isLoading: false,
          isError: true
        };

      case 'RESET':
        return { ...state,
          isLoading: false,
          isError: false,
          hits: []
        };

      default:
        throw new Error();
    }
  }; // call `dispatchResults` to change `results` object


  const [results, dispatchResults] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useReducer(resultsReducer, resultsInitialState);

  const handleSearch = event => {
    setSearchTerm(event.target.value);
  };

  react__WEBPACK_IMPORTED_MODULE_0___default.a.useEffect(() => {
    if (!searchTerm) {
      dispatchResults({
        type: 'RESET'
      });
      return;
    }

    if (searchTerm.length < 3) {
      return;
    } // Mark search form as initializing/loading


    dispatchResults({
      type: 'SEARCH_FETCH_INIT'
    }); // Fetch from API

    const url = API_ENDPOINT + searchTerm;
    fetch(url).then(response => response.json()).then(result => {
      console.log('fetch result', result);

      if (!result.collection.items.length) {
        dispatchResults({
          type: 'SEARCH_FETCH_FAIL'
        });
      } else {
        dispatchResults({
          type: 'SEARCH_FETCH_SUCCESS',
          payload: result.collection.items
        });
      }
    }).catch(() => dispatchResults({
      type: 'SEARCH_FETCH_FAIL'
    }));
  }, [searchTerm]);
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("fieldset", {
    className: "inputwithlabel"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("label", {
    htmlFor: "searchform"
  }, "Search:"), ' ', /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("input", {
    id: "searchform",
    type: "text",
    value: searchTerm,
    placeholder: "Search all the things",
    onChange: handleSearch
  }), ' ', /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    type: "reset",
    onClick: () => setSearchTerm('')
  }, "Reset"), results.isError && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Something went wrong"), results.isLoading ? /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Loading...") : /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(SearchResults, {
    results: results
  }));
}

function SearchResults(props) {
  const {
    results
  } = props;
  if (results.hits.length === 0) return null;
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("ul", null, results.hits.map(item => /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(SearchResult, {
    key: item.title_sort,
    item: item
  })));
}
/**
 * Item component
 * @param {Object} props.item Item object
 * @param {} onRemoveItem
 */


function SearchResult(props) {
  const {
    item
  } = props;
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("li", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: item.links.page
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("dfn", null, item.title), ' ', /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", null, "(", item.type, ")")));
} // ReactDOM.render(
//     React.createElement(Search),
//     document.getElementById('search'),
// );

/***/ }),

/***/ "./assets/javascript/storageAvailable.js":
/*!***********************************************!*\
  !*** ./assets/javascript/storageAvailable.js ***!
  \***********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return storageAvailable; });
// Usage:
// if (storageAvailable('localStorage')) {/** */}
function storageAvailable(type) {
  let storage;

  try {
    storage = window[type];
    const x = '__storage_test__';
    storage.setItem(x, x);
    storage.removeItem(x);
    return true;
  } catch (e) {
    return e instanceof DOMException && ( // everything except Firefox
    e.code === 22 // Firefox
    || e.code === 1014 // test name field too, because code might not be present
    // everything except Firefox
    || e.name === 'QuotaExceededError' // Firefox
    || e.name === 'NS_ERROR_DOM_QUOTA_REACHED') // acknowledge QuotaExceededError only if there's something already stored
    && storage && storage.length !== 0;
  }
}

/***/ }),

/***/ "./assets/styles/app.scss":
/*!********************************!*\
  !*** ./assets/styles/app.scss ***!
  \********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var api = __webpack_require__(/*! ../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
            var content = __webpack_require__(/*! !../../node_modules/css-loader/dist/cjs.js!../../node_modules/sass-loader/dist/cjs.js!./app.scss */ "./node_modules/css-loader/dist/cjs.js!./node_modules/sass-loader/dist/cjs.js!./assets/styles/app.scss");

            content = content.__esModule ? content.default : content;

            if (typeof content === 'string') {
              content = [[module.i, content, '']];
            }

var options = {};

options.insert = "head";
options.singleton = false;

var update = api(content, options);



module.exports = content.locals || {};

/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js!./node_modules/sass-loader/dist/cjs.js!./assets/styles/app.scss":
/*!*************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js!./node_modules/sass-loader/dist/cjs.js!./assets/styles/app.scss ***!
  \*************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(true);
// Module
___CSS_LOADER_EXPORT___.push([module.i, ".a {\n  color: #06C;\n  text-decoration: underline;\n  cursor: pointer;\n}\n.a:active {\n  color: #6B3EA8;\n}\n.a:hover {\n  color: #39F;\n  border-color: #39F;\n}\n\n.red {\n  color: #D33;\n}\n.red:hover {\n  color: #F17878;\n}\n\nbody {\n  background-color: #EEE;\n}\n\n.hello {\n  color: pink;\n  font-weight: bold;\n}", "",{"version":3,"sources":["webpack://assets/styles/app.scss"],"names":[],"mappings":"AAKA;EACE,WANU;EAOV,0BAAA;EACA,eAAA;AAJF;AAKC;EAAW,cAAA;AAFZ;AAGC;EAAU,WATO;EASkB,kBATlB;AAUlB;;AAEA;EACC,WAZI;AAaL;AAAC;EAAU,cAAA;AAGX;;AAEA;EAEI,sBAJoB;AAIxB;;AAIA;EACE,WAAA;EACA,iBAAA;AADF","sourcesContent":["$link-color:#06C;\r\n$link-hover-color:#39F;\r\n$red:#D33;\r\n$green:#00A264;\r\n\r\n.a {\r\n  color: $link-color;\r\n  text-decoration: underline;\r\n  cursor: pointer;\r\n\t&:active { color:#6B3EA8; }\r\n\t&:hover { color:$link-hover-color; border-color:$link-hover-color; }\r\n}\r\n\r\n.red {\r\n\tcolor: $red;\r\n\t&:hover { color:#F17878; }\r\n}\r\n\r\n$body-background-color: #EEE;\r\n\r\nbody {\r\n  background: {\r\n    color: $body-background-color;\r\n  }\r\n}\r\n\r\n.hello {\r\n  color: pink;\r\n  font-weight: bold;\r\n}"],"sourceRoot":""}]);
// Exports
/* harmony default export */ __webpack_exports__["default"] = (___CSS_LOADER_EXPORT___);


/***/ })

/******/ });
//# sourceMappingURL=app_bundle.js.map