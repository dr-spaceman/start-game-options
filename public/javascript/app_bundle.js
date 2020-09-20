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

/***/ "./assets/images/colophon_welcome.png":
/*!********************************************!*\
  !*** ./assets/images/colophon_welcome.png ***!
  \********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMAAAAA0CAMAAADrAcc6AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAABhQTFRF/6NHuPgY////518T+DgAAAAAixcA////euOm3AAAAAh0Uk5T/////////wDeg71ZAAACMElEQVR42txXibLCMAjEBNL//+OXo6naqQrJBsfHqHU9lmwgUGizGu9PjVG1dfzFxWb5Le/cSgeBAuVHWMVvFMCVnw3sRFIfq/gHBHAx9b/sAmz81hRq9IY94lCOQOBV/DYBVPaHMr9oXXDYjdfwGwWUvaHjRfWHECRbFsAr+McEUIuysGr9NQasU2DlHxFA1F18dpB/FrgJKG8IzW8WkDlbW9o3SSGAuwBWCLDy2wXUAFOtK1oBUtOoXBQCjPxDAkpXrS6UOS09oZnx/GNnoFbFJQLM/OMCtPzcG5OurJv57bcS1UVLUVNn5VX85luJzbSgrdZFtmyojb8JEElJnmwWZ7tVk2vD+qOM+ydp/34OH+t/oQDsj1Lqn7TLLC57cgig6/Uj/bUIHN8ArkK3w+hFBID+VgiIff3RTcBjbGdxnsBilRAj0ZszAPJ3ikCSaVxCUBTkF5JPEZj3tyCFJJbFFxFRHFIo7UqeBE7gIqAfghhfVSGcv2cBZwcDuGaQVgAAEyr1LwRcrV/Q/laUUepn4CbufQBShe6NzKMKPRw+OR3GIdzKUMugdymE8nc0snYy7o1mGLc6VJ4xvrmZQ/kjSe0GdQ+PTOPDrteP9kc9Hg/9eQrLB0P7o3MNmcUKAVB/CyYyX4yfyJwxfCLzxvhO7Hz9JwKQE5kzxt8LOePfTyH0hOSN4ROZN6YfTf2FE9l3+8APViH0hOSN8ROZM8ZPZM4YPpF5Y/hE5o3/BBgACz7ema3m3fAAAAAASUVORK5CYII=");

/***/ }),

/***/ "./assets/images/textured_bg.jpg":
/*!***************************************!*\
  !*** ./assets/images/textured_bg.jpg ***!
  \***************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ("data:image/jpeg;base64,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");

/***/ }),

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
/* harmony import */ var normalize_css__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! normalize.css */ "./node_modules/normalize.css/normalize.css");
/* harmony import */ var normalize_css__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(normalize_css__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _styles_app_scss__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../styles/app.scss */ "./assets/styles/app.scss");
/* harmony import */ var _styles_app_scss__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_styles_app_scss__WEBPACK_IMPORTED_MODULE_5__);

 // Components to render


 // Stylesheets that get injected into <head>



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
/* harmony import */ var _images_colophon_welcome_png__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../images/colophon_welcome.png */ "./assets/images/colophon_welcome.png");



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
      className: "container dark",
      style: {
        position: 'fixed',
        zIndex: 999,
        right: 0,
        bottom: 0,
        left: 0,
        fontSize: '15px',
        color: '#BBB',
        boxShadow: '0 0 10px -5px black'
      }
    }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
      style: {
        width: '960px',
        padding: '30px 0',
        margin: '0 auto',
        textAlign: 'center'
      }
    }, "Welcome to Videogam.in, a site about videogames.", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("br", null), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
      href: "/about.php"
    }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("strong", null, "Read more")), " about this site or else ", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
      href: "#close",
      title: "hide this message and don't show it to me again",
      className: "tooltip",
      onClick: handleClose
    }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("strong", null, "pay me for the door repair charge")), "."), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
      style: {
        position: 'absolute',
        zIndex: 2,
        top: '10px',
        left: '50%',
        width: '192px',
        height: '16px',
        margin: '0 0 0 -96px',
        background: `url(${_images_colophon_welcome_png__WEBPACK_IMPORTED_MODULE_2__["default"]}) no-repeat 0 0`
      }
    }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
      style: {
        position: 'absolute',
        zIndex: 2,
        bottom: '0',
        left: '50%',
        width: '192px',
        height: '18px',
        margin: '0 0 0 -96px',
        background: `url(${_images_colophon_welcome_png__WEBPACK_IMPORTED_MODULE_2__["default"]}) no-repeat 0 -16px`
      }
    }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
      style: {
        position: 'absolute',
        zIndex: 1,
        right: '0',
        bottom: '0',
        left: '0',
        width: '100%',
        height: '18px',
        background: `url(${_images_colophon_welcome_png__WEBPACK_IMPORTED_MODULE_2__["default"]}) repeat-x 0 -34px`
      }
    }));
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
            var content = __webpack_require__(/*! !../../node_modules/css-loader/dist/cjs.js!../../node_modules/resolve-url-loader!../../node_modules/sass-loader/dist/cjs.js!./app.scss */ "./node_modules/css-loader/dist/cjs.js!./node_modules/resolve-url-loader/index.js!./node_modules/sass-loader/dist/cjs.js!./assets/styles/app.scss");

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

/***/ "./node_modules/css-loader/dist/cjs.js!./node_modules/resolve-url-loader/index.js!./node_modules/sass-loader/dist/cjs.js!./assets/styles/app.scss":
/*!***********************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js!./node_modules/resolve-url-loader!./node_modules/sass-loader/dist/cjs.js!./assets/styles/app.scss ***!
  \***********************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../node_modules/css-loader/dist/runtime/getUrl.js */ "./node_modules/css-loader/dist/runtime/getUrl.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _images_textured_bg_jpg__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../images/textured_bg.jpg */ "./assets/images/textured_bg.jpg");
// Imports



var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(true);
var ___CSS_LOADER_URL_REPLACEMENT_0___ = _node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_1___default()(_images_textured_bg_jpg__WEBPACK_IMPORTED_MODULE_2__["default"]);
// Module
___CSS_LOADER_EXPORT___.push([module.i, ".a {\n  color: #06C;\n  text-decoration: underline;\n  cursor: pointer;\n}\n.a:active {\n  color: #6B3EA8;\n}\n.a:hover {\n  color: #39F;\n  border-color: #39F;\n}\n\n.red {\n  color: #D33;\n}\n.red:hover {\n  color: #F17878;\n}\n\nbody {\n  background-color: #EEE;\n  background-image: url(" + ___CSS_LOADER_URL_REPLACEMENT_0___ + ");\n  border-top: 3px solid #222;\n  font-size: 13px;\n  font-family: Helvetica, Arial;\n  text-align: left;\n  vertical-align: top;\n}\n\n.hello {\n  color: pink;\n  font-weight: bold;\n}\n\n.dark {\n  background-color: black;\n  color: white;\n}\n.dark a {\n  color: white;\n}\n.dark a:hover {\n  color: lightgray;\n}", "",{"version":3,"sources":["webpack://app.scss"],"names":[],"mappings":"AAMA;EACE,WAPU;EAQV,0BAAA;EACA,eAAA;AALF;AAMC;EAAW,cAAA;AAHZ;AAIC;EAAU,WAVO;EAUkB,kBAVlB;AAUlB;;AAGA;EACC,WAbI;AAaL;AACC;EAAU,cAAA;AAEX;;AAGA;EAEI,sBAJoB;EAKpB,yDAAA;EAEF,0BAAA;EACA,eAAA;EACA,6BAAA;EACA,gBAAA;EACA,mBAAA;AAFF;;AAKA;EACE,WAAA;EACA,iBAAA;AAFF;;AAKA;EACE,uBAAA;EACA,YAAA;AAFF;AAGE;EACE,YAAA;AADJ;AAEI;EACE,gBAAA;AAAN","sourcesContent":["$link-color:#06C;\r\n$link-hover-color:#39F;\r\n$red:#D33;\r\n$green:#00A264;\r\n$img-dir: '../images';\r\n\r\n.a {\r\n  color: $link-color;\r\n  text-decoration: underline;\r\n  cursor: pointer;\r\n\t&:active { color:#6B3EA8; }\r\n\t&:hover { color:$link-hover-color; border-color:$link-hover-color; }\r\n}\r\n\r\n.red {\r\n\tcolor: $red;\r\n\t&:hover { color:#F17878; }\r\n}\r\n\r\n$body-background-color: #EEE;\r\n\r\nbody {\r\n  background: {\r\n    color: $body-background-color;\r\n    image: url('#{$img-dir}/textured_bg.jpg');\r\n  }\r\n  border-top: 3px solid #222;\r\n  font-size: 13px;\r\n  font-family: Helvetica, Arial;\r\n  text-align: left;\r\n  vertical-align: top;\r\n}\r\n\r\n.hello {\r\n  color: pink;\r\n  font-weight: bold;\r\n}\r\n\r\n.dark {\r\n  background-color: black;\r\n  color: white;\r\n  a {\r\n    color: white;\r\n    &:hover {\r\n      color: lightgray\r\n    }\r\n  }\r\n}"],"sourceRoot":""}]);
// Exports
/* harmony default export */ __webpack_exports__["default"] = (___CSS_LOADER_EXPORT___);


/***/ })

/******/ });
//# sourceMappingURL=app_bundle.js.map