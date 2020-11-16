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
/******/ 	// object to store loaded CSS chunks
/******/ 	var installedCssChunks = {
/******/ 		"index": 0
/******/ 	};
/******/
/******/ 	// object to store loaded and loading chunks
/******/ 	// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 	// Promise = chunk loading, 0 = chunk loaded
/******/ 	var installedChunks = {
/******/ 		"index": 0
/******/ 	};
/******/
/******/ 	var deferredModules = [];
/******/
/******/ 	// script path function
/******/ 	function jsonpScriptSrc(chunkId) {
/******/ 		return __webpack_require__.p + "" + ({"login":"login","top-nav-user":"top-nav-user"}[chunkId]||chunkId) + ".bundle.js"
/******/ 	}
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
/******/ 	// This file contains only the entry chunk.
/******/ 	// The chunk loading function for additional chunks
/******/ 	__webpack_require__.e = function requireEnsure(chunkId) {
/******/ 		var promises = [];
/******/
/******/
/******/ 		// mini-css-extract-plugin CSS loading
/******/ 		var cssChunks = {"login":1};
/******/ 		if(installedCssChunks[chunkId]) promises.push(installedCssChunks[chunkId]);
/******/ 		else if(installedCssChunks[chunkId] !== 0 && cssChunks[chunkId]) {
/******/ 			promises.push(installedCssChunks[chunkId] = new Promise(function(resolve, reject) {
/******/ 				var href = "" + ({"login":"login","top-nav-user":"top-nav-user"}[chunkId]||chunkId) + ".css";
/******/ 				var fullhref = __webpack_require__.p + href;
/******/ 				var existingLinkTags = document.getElementsByTagName("link");
/******/ 				for(var i = 0; i < existingLinkTags.length; i++) {
/******/ 					var tag = existingLinkTags[i];
/******/ 					var dataHref = tag.getAttribute("data-href") || tag.getAttribute("href");
/******/ 					if(tag.rel === "stylesheet" && (dataHref === href || dataHref === fullhref)) return resolve();
/******/ 				}
/******/ 				var existingStyleTags = document.getElementsByTagName("style");
/******/ 				for(var i = 0; i < existingStyleTags.length; i++) {
/******/ 					var tag = existingStyleTags[i];
/******/ 					var dataHref = tag.getAttribute("data-href");
/******/ 					if(dataHref === href || dataHref === fullhref) return resolve();
/******/ 				}
/******/ 				var linkTag = document.createElement("link");
/******/
/******/ 				linkTag.rel = "stylesheet";
/******/ 				linkTag.type = "text/css";
/******/ 				linkTag.onload = resolve;
/******/ 				linkTag.onerror = function(event) {
/******/ 					var request = event && event.target && event.target.href || fullhref;
/******/ 					var err = new Error("Loading CSS chunk " + chunkId + " failed.\n(" + request + ")");
/******/ 					err.code = "CSS_CHUNK_LOAD_FAILED";
/******/ 					err.request = request;
/******/ 					delete installedCssChunks[chunkId]
/******/ 					linkTag.parentNode.removeChild(linkTag)
/******/ 					reject(err);
/******/ 				};
/******/ 				linkTag.href = fullhref;
/******/
/******/ 				document.head.appendChild(linkTag);
/******/ 			}).then(function() {
/******/ 				installedCssChunks[chunkId] = 0;
/******/ 			}));
/******/ 		}
/******/
/******/ 		// JSONP chunk loading for javascript
/******/
/******/ 		var installedChunkData = installedChunks[chunkId];
/******/ 		if(installedChunkData !== 0) { // 0 means "already installed".
/******/
/******/ 			// a Promise means "currently loading".
/******/ 			if(installedChunkData) {
/******/ 				promises.push(installedChunkData[2]);
/******/ 			} else {
/******/ 				// setup Promise in chunk cache
/******/ 				var promise = new Promise(function(resolve, reject) {
/******/ 					installedChunkData = installedChunks[chunkId] = [resolve, reject];
/******/ 				});
/******/ 				promises.push(installedChunkData[2] = promise);
/******/
/******/ 				// start chunk loading
/******/ 				var script = document.createElement('script');
/******/ 				var onScriptComplete;
/******/
/******/ 				script.charset = 'utf-8';
/******/ 				script.timeout = 120;
/******/ 				if (__webpack_require__.nc) {
/******/ 					script.setAttribute("nonce", __webpack_require__.nc);
/******/ 				}
/******/ 				script.src = jsonpScriptSrc(chunkId);
/******/
/******/ 				// create error before stack unwound to get useful stacktrace later
/******/ 				var error = new Error();
/******/ 				onScriptComplete = function (event) {
/******/ 					// avoid mem leaks in IE.
/******/ 					script.onerror = script.onload = null;
/******/ 					clearTimeout(timeout);
/******/ 					var chunk = installedChunks[chunkId];
/******/ 					if(chunk !== 0) {
/******/ 						if(chunk) {
/******/ 							var errorType = event && (event.type === 'load' ? 'missing' : event.type);
/******/ 							var realSrc = event && event.target && event.target.src;
/******/ 							error.message = 'Loading chunk ' + chunkId + ' failed.\n(' + errorType + ': ' + realSrc + ')';
/******/ 							error.name = 'ChunkLoadError';
/******/ 							error.type = errorType;
/******/ 							error.request = realSrc;
/******/ 							chunk[1](error);
/******/ 						}
/******/ 						installedChunks[chunkId] = undefined;
/******/ 					}
/******/ 				};
/******/ 				var timeout = setTimeout(function(){
/******/ 					onScriptComplete({ type: 'timeout', target: script });
/******/ 				}, 120000);
/******/ 				script.onerror = script.onload = onScriptComplete;
/******/ 				document.head.appendChild(script);
/******/ 			}
/******/ 		}
/******/ 		return Promise.all(promises);
/******/ 	};
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
/******/ 	__webpack_require__.p = "/dist/";
/******/
/******/ 	// on error function for async loading
/******/ 	__webpack_require__.oe = function(err) { console.error(err); throw err; };
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
/******/ 	deferredModules.push(["./browser/src/index.js","vendor"]);
/******/ 	// run deferred modules when ready
/******/ 	return checkDeferredModules();
/******/ })
/************************************************************************/
/******/ ({

/***/ "./browser/images/colophon_welcome.png":
/*!*********************************************!*\
  !*** ./browser/images/colophon_welcome.png ***!
  \*********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMAAAAA0CAMAAADrAcc6AAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAAPUExURf+jR/////g4AIsXAAAAALEBmA0AAAHaSURBVHja3FdbksMgDDOG+595eWaT/MQC4U7CdJtV2koRNhhLQof2P8uQOvbxF4mEfFc7t1EgSJD8Crv4QQNa+RVgz5PfXpv4JwxoGeZf4QYwfjSFGj0wRxrKEgi6ix8zIGV+pPIbJTT0oXv4QQNlbuR4M/0g1OWbDegO/jkDYo5yef4aA7U5QPlnDIgMiWeB/LWgzUD5R9j8sIHM2cpSnySDAR0G1GAA5ccN1ABL3VesBloa1c1R2PxTBkpVrRLGnE4joVX5/HNroO6KWwzA/PMGrPzj+Y1LEubHjxJVoqUoVFl1Fz98lEjQA6W6LyoyoRj/MBDj9dYqfhpcPcl43In98zX8/PxUPYlx3GmXVWx5fqZei8DxCeFqcsC7fsTAObar2LoGSHq3CMS0jKEIrOu9P4Vid3IxuIBtuxBP72rgLjCBIQMELKzUxwzw9L5WB164C51174txBtuPEhy9o5C1lfFfaKaxsZCx9CTFdkDt4UnL+NEAV09GPE71eQkbAkDVk/sesoqBDKLobejIfDG/I3PG9I7MG/MrsfP1IwaYHZkz5p+FnPH7U4jdIXljekfmjeWlqb+xI/ttHXjhLsTukLwxvyNzxvyOzBnTOzJvTO/IvPGfAAMAaBGIhRSw7HkAAAAASUVORK5CYII=");

/***/ }),

/***/ "./browser/images/icons/loading_mascot.gif":
/*!*************************************************!*\
  !*** ./browser/images/icons/loading_mascot.gif ***!
  \*************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ("data:image/gif;base64,R0lGODlhEQAQAMQQACkoKVpZMefDQt2IVPfXWrXPjBhJ3iFhQmswGJxpEGO+Y//nrUKWUt6uAPeOQsZJGP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh+QQJFAAQACwAAAAAEQAQAAAFjSAkjtDjPGRKPoxzJmLTiAkMMYzSPs2yNAQIsJGQNXK4hcHwgxQJPV9Dx1AyYYSi9cd4eKUwYHW5SJhcvMTBGciBTYgT47CGHBR43YMAiD90dTkKBQV7AH0neHV3BT4miA8FgCIHDFFnJ4VeKCUOlo5ehScnnSY4RjKYKJsnD5U4CHEnCCNesSoID7QQIQAh+QQFFAAQACwAAAAAEQAQAAAFjCAkjtDjPGRKPoxzJmLTiAkMMYzSPs2yNAQIsJGQNXK4hcHwgxQJPV9Dx1AyYYSi9cd4eKUwYHW5SJhcvMTBGciBTYgT47CGHBR43YMAiD90dTkKBQUmfSd/CnV3BT5nJ4SAIl4PA45ekQJzkycnPj4FCponnGhGMjidKAhnCAg4c16IKBCsCCquXiIhADs=");

/***/ }),

/***/ "./browser/images/icons/questionblock.png":
/*!************************************************!*\
  !*** ./browser/images/icons/questionblock.png ***!
  \************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ("data:image/png;base64,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");

/***/ }),

/***/ "./browser/src/components/layout/Colophon.jsx":
/*!****************************************************!*\
  !*** ./browser/src/components/layout/Colophon.jsx ***!
  \****************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Colophon; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _lib_storage_available_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../lib/storage-available.js */ "./browser/src/lib/storage-available.js");
/* harmony import */ var _images_colophon_welcome_png__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../images/colophon_welcome.png */ "./browser/images/colophon_welcome.png");



function Colophon() {
  if (!Object(_lib_storage_available_js__WEBPACK_IMPORTED_MODULE_1__["default"])('localStorage')) {
    return '';
  } // Prevent saving hidden status:
  // localStorage.clear();


  const [open, setOpen] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState(true);

  function handleClose(event) {
    event.preventDefault();
    setOpen(false);
    localStorage.setItem('colophon', 'closed');
  }

  if (open && localStorage.getItem('colophon') !== 'closed') {
    return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
      className: "container dark type-retro",
      style: {
        position: 'fixed',
        zIndex: 999,
        right: 0,
        bottom: 0,
        left: 0,
        color: '#BBB'
      }
    }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
      style: {
        padding: '50px 0 40px 0',
        margin: '0 auto',
        textAlign: 'center'
      }
    }, "Start Game is the secret", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("br", null), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
      href: "#close",
      title: "hide this message and don't show it to me again",
      className: "tooltip",
      onClick: handleClose
    }, "Now Pay me for the door repair charge")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
      style: {
        position: 'absolute',
        zIndex: 2,
        top: '20px',
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

/***/ "./browser/src/components/layout/Header.jsx":
/*!**************************************************!*\
  !*** ./browser/src/components/layout/Header.jsx ***!
  \**************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Header; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _TopNav_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TopNav.jsx */ "./browser/src/components/layout/TopNav.jsx");
/* harmony import */ var _Search_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Search.jsx */ "./browser/src/components/layout/Search.jsx");
/* harmony import */ var _ui_Button_jsx__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../ui/Button.jsx */ "./browser/src/components/ui/Button.jsx");
/* harmony import */ var _lib_icons_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../../lib/icons.js */ "./browser/src/lib/icons.js");





const TopNavUser = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.lazy(() => __webpack_require__.e(/*! import() | top-nav-user */ "top-nav-user").then(__webpack_require__.bind(null, /*! ./TopNavUser.jsx */ "./browser/src/components/layout/TopNavUser.jsx")));
const Login = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.lazy(() => __webpack_require__.e(/*! import() | login */ "login").then(__webpack_require__.bind(null, /*! ./Login.jsx */ "./browser/src/components/layout/Login.jsx")));

function HeaderUser({
  username
}) {
  const [loginLoaded, setLoginLoaded] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState(false);

  const lazyloadLogin = event => {
    event.preventDefault();
    setLoginLoaded(true);
  };

  const LoginButton = ({
    handleClick
  }) => /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_Button_jsx__WEBPACK_IMPORTED_MODULE_3__["default"], {
    title: "Login",
    onClick: handleClick,
    classes: {
      'button-header': true
    }
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_lib_icons_js__WEBPACK_IMPORTED_MODULE_4__["QuestionBlock"], null));

  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "login"
  }, username ? /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Suspense, {
    fallback: "User"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(TopNavUser, {
    username: username
  })) : /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, loginLoaded ? /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Suspense, {
    fallback: /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_lib_icons_js__WEBPACK_IMPORTED_MODULE_4__["LoadingMascot"], null)
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(Login, {
    LoginButton: LoginButton
  })) : /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(LoginButton, {
    handleClick: lazyloadLogin
  })));
}

function Header(props) {
  const {
    username
  } = props;
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_TopNav_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], null), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(HeaderUser, {
    username: username
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Search_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], null));
}

/***/ }),

/***/ "./browser/src/components/layout/Search.jsx":
/*!**************************************************!*\
  !*** ./browser/src/components/layout/Search.jsx ***!
  \**************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Search; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react_icons_bi__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react-icons/bi */ "./node_modules/react-icons/bi/index.esm.js");
/* harmony import */ var _ui_Modal_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../ui/Modal.jsx */ "./browser/src/components/ui/Modal.jsx");
/* harmony import */ var _ui_Button_jsx__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../ui/Button.jsx */ "./browser/src/components/ui/Button.jsx");
/* eslint-disable react/button-has-type */




const API_ENDPOINT = "/api/search?q=";
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
      dispatchResults({
        type: 'SEARCH_FETCH_SUCCESS',
        payload: result.collection.items
      });
    }).catch(() => dispatchResults({
      type: 'SEARCH_FETCH_FAIL'
    }));
  }, [searchTerm]);
  const [open, setOpen] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState(false);

  const handleClose = () => {
    setOpen(false);
  };

  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "search"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_Button_jsx__WEBPACK_IMPORTED_MODULE_3__["default"], {
    classes: {
      'button-header': true
    },
    onClick: () => setOpen(true)
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react_icons_bi__WEBPACK_IMPORTED_MODULE_1__["BiSearch"], {
    size: "28",
    title: "Search"
  })), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_Modal_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], {
    open: open,
    close: handleClose,
    closeTabIndex: "0"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("input", {
    id: "searchform",
    type: "text",
    value: searchTerm,
    placeholder: "Search all the things",
    tabIndex: "0",
    onChange: handleSearch,
    ref: input => input && input.focus()
  }), ' ', /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    type: "reset",
    tabIndex: "0",
    onClick: () => setSearchTerm('')
  }, "Reset"), results.isError && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Something went wrong"), results.isLoading ? /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Loading...") : /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(SearchResults, {
    results: results
  }))));
}

function SearchResults(props) {
  const {
    results
  } = props;

  if (results.hits.length === 0) {
    return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "No results found");
  }

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

/***/ "./browser/src/components/layout/TopNav.jsx":
/*!**************************************************!*\
  !*** ./browser/src/components/layout/TopNav.jsx ***!
  \**************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return TopNav; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react_transition_group__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react-transition-group */ "./node_modules/react-transition-group/esm/index.js");
/* harmony import */ var _ui_NavMenu_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../ui/NavMenu.jsx */ "./browser/src/components/ui/NavMenu.jsx");
/* harmony import */ var _ui_Button_jsx__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../ui/Button.jsx */ "./browser/src/components/ui/Button.jsx");




function TopNav() {
  const [open, setOpen] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState(false);

  const toggleOpen = () => {
    setOpen(!open);
  };

  const buttonClasses = {
    active: open,
    inactive: !open
  };
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react_transition_group__WEBPACK_IMPORTED_MODULE_1__["CSSTransition"], {
    in: open,
    timeout: 1500
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_NavMenu_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_NavMenu_jsx__WEBPACK_IMPORTED_MODULE_2__["default"].Item, {
    selected: true
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("h6", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "/games"
  }, "Start Game"))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_NavMenu_jsx__WEBPACK_IMPORTED_MODULE_2__["default"].Item, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_Button_jsx__WEBPACK_IMPORTED_MODULE_3__["default"], {
    role: "switch",
    "aria-checked": open,
    id: "menu",
    classes: buttonClasses,
    onClick: toggleOpen
  }, "Options")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_NavMenu_jsx__WEBPACK_IMPORTED_MODULE_2__["default"].Item, {
    className: "hidden"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "/games"
  }, "Games")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_NavMenu_jsx__WEBPACK_IMPORTED_MODULE_2__["default"].Item, {
    className: "hidden"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "/people"
  }, "People")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_NavMenu_jsx__WEBPACK_IMPORTED_MODULE_2__["default"].Item, {
    className: "hidden"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "/music"
  }, "Music"))));
}

/***/ }),

/***/ "./browser/src/components/ui/Button.jsx":
/*!**********************************************!*\
  !*** ./browser/src/components/ui/Button.jsx ***!
  \**********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! classnames */ "./node_modules/classnames/index.js");
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(classnames__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! prop-types */ "./node_modules/prop-types/index.js");
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(prop_types__WEBPACK_IMPORTED_MODULE_2__);
function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }





function Button({
  color,
  classes,
  size,
  type,
  variant,
  children,
  ...props
}) {
  const className = classnames__WEBPACK_IMPORTED_MODULE_1___default()({ ...classes,
    [`button-color-${color}`]: color,
    [`button-size-${size}`]: size,
    [`button-${variant}`]: variant
  });
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", _extends({
    className: className,
    type: type
  }, props), children);
}

Button.propTypes = {
  color: prop_types__WEBPACK_IMPORTED_MODULE_2___default.a.oneOf(['default', 'primary', 'red', 'green', 'dark', 'light']),
  classes: prop_types__WEBPACK_IMPORTED_MODULE_2___default.a.any,
  size: prop_types__WEBPACK_IMPORTED_MODULE_2___default.a.oneOf(['small', 'medium', 'large']),
  type: prop_types__WEBPACK_IMPORTED_MODULE_2___default.a.oneOf(['button', 'submit', 'reset']),
  variant: prop_types__WEBPACK_IMPORTED_MODULE_2___default.a.oneOf(['text', 'contained', 'outlined', 'link', 'close']),
  children: prop_types__WEBPACK_IMPORTED_MODULE_2___default.a.node
};
Button.defaultProps = {
  type: 'button',
  variant: 'text'
};
/* harmony default export */ __webpack_exports__["default"] = (Button);

/***/ }),

/***/ "./browser/src/components/ui/Modal.jsx":
/*!*********************************************!*\
  !*** ./browser/src/components/ui/Modal.jsx ***!
  \*********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Modal; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react_transition_group__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react-transition-group */ "./node_modules/react-transition-group/esm/index.js");
/* harmony import */ var react_icons_bi__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! react-icons/bi */ "./node_modules/react-icons/bi/index.esm.js");



function Modal({
  children,
  open = true,
  close = null,
  timeout = 500,
  overlay = true,
  closeButton = true
}) {
  const CloseButton = () => closeButton && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    type: "button",
    role: "switch",
    "aria-checked": open,
    "aria-label": "Close",
    className: "modal-close button-close",
    onClick: close
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react_icons_bi__WEBPACK_IMPORTED_MODULE_2__["BiX"], {
    "arial-hidden": "true"
  }));

  const Overlay = () => overlay && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: "modal-overlay",
    role: "button",
    onClick: close,
    "aria-hidden": "true",
    "aria-label": "close"
  });

  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react_transition_group__WEBPACK_IMPORTED_MODULE_1__["CSSTransition"], {
    in: open,
    timeout: timeout,
    classNames: "modal",
    unmountOnExit: true
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: "modal modal-container"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(Overlay, null), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: "modal-content light"
  }, children, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(CloseButton, null))));
}

/***/ }),

/***/ "./browser/src/components/ui/NavMenu.jsx":
/*!***********************************************!*\
  !*** ./browser/src/components/ui/NavMenu.jsx ***!
  \***********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! prop-types */ "./node_modules/prop-types/index.js");
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(prop_types__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! classnames */ "./node_modules/classnames/index.js");
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(classnames__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _lib_match_component_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../lib/match-component.js */ "./browser/src/lib/match-component.js");
function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }





const isNavMenuItem = Object(_lib_match_component_js__WEBPACK_IMPORTED_MODULE_3__["default"])(NavMenuItem);
/**
 * UI component that emulates a videogame select/title screen. A selected item is highlighted with
 * a caret.
 */

function NavMenu({
  className,
  children,
  ...props
}) {
  let initSelected;
  react__WEBPACK_IMPORTED_MODULE_0___default.a.Children.forEach(children, (child, index) => {
    if (child.props.selected) {
      initSelected = index;
    }
  }); // Set to index of <NavMenu.Item> child

  const [selected, setSelected] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState(initSelected);
  const classNames = classnames__WEBPACK_IMPORTED_MODULE_2___default()(className, {
    navmenu: true
  }); // Find first <NavMenuItem /> child to insert tabIndex prop

  let firstValidChild;
  let childProps;
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("nav", _extends({
    className: classNames
  }, props), react__WEBPACK_IMPORTED_MODULE_0___default.a.Children.map(children, (child, index) => {
    if (! /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.isValidElement(child)) {
      return child;
    }

    if (isNavMenuItem(child)) {
      firstValidChild = firstValidChild || index;
      childProps = { ...child.props,
        index,
        setSelected,
        selected: selected === index,
        tabIndex: firstValidChild === index ? 0 : -1
      };
      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.cloneElement(child, childProps);
    }

    return child;
  }));
}

NavMenu.propTypes = {
  className: prop_types__WEBPACK_IMPORTED_MODULE_1___default.a.string,
  children: prop_types__WEBPACK_IMPORTED_MODULE_1___default.a.node.isRequired
};
NavMenu.defaultProps = {
  className: ''
};

function NavMenuItem({
  index,
  caret,
  selected,
  setSelected,
  className,
  children,
  ...props
}) {
  const handleClick = event => {
    setSelected(index);
  };

  const classNames = classnames__WEBPACK_IMPORTED_MODULE_2___default()(className, {
    'navmenu-item': true,
    selected
  });
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", _extends({
    className: classNames,
    role: "menuitem",
    onClick: handleClick,
    "aria-hidden": "true"
  }, props), caret && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: "navmenu-caret"
  }, ">\xA0"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: "navmenu-item-content"
  }, children));
}

NavMenuItem.propTypes = {
  index: prop_types__WEBPACK_IMPORTED_MODULE_1___default.a.number,

  /** Prepend a caret on the menu item */
  caret: prop_types__WEBPACK_IMPORTED_MODULE_1___default.a.bool,

  /** Determines if the caret is visible on initial render. */
  selected: prop_types__WEBPACK_IMPORTED_MODULE_1___default.a.bool,
  setSelected: prop_types__WEBPACK_IMPORTED_MODULE_1___default.a.func,
  className: prop_types__WEBPACK_IMPORTED_MODULE_1___default.a.string,
  children: prop_types__WEBPACK_IMPORTED_MODULE_1___default.a.node
};
NavMenuItem.defaultProps = {
  caret: true,
  selected: false,
  className: ''
};
NavMenu.Item = NavMenuItem;
/* harmony default export */ __webpack_exports__["default"] = (NavMenu);

/***/ }),

/***/ "./browser/src/index.js":
/*!******************************!*\
  !*** ./browser/src/index.js ***!
  \******************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react_dom__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react-dom */ "./node_modules/react-dom/index.js");
/* harmony import */ var react_dom__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(react_dom__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var normalize_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! normalize.css */ "./node_modules/normalize.css/normalize.css");
/* harmony import */ var _styles_app_scss__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../styles/app.scss */ "./browser/styles/app.scss");
/* harmony import */ var _components_layout_Colophon_jsx__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./components/layout/Colophon.jsx */ "./browser/src/components/layout/Colophon.jsx");
/* harmony import */ var _components_layout_Header_jsx__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./components/layout/Header.jsx */ "./browser/src/components/layout/Header.jsx");
// Entry point for React components on all pages

 // Stylesheets that get injected into <head>


 // Components to render


 // Grab data-* properties from <header> element and pass them as props to <Header> component

const headerElement = document.getElementById('header');
react_dom__WEBPACK_IMPORTED_MODULE_1___default.a.render( /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_components_layout_Header_jsx__WEBPACK_IMPORTED_MODULE_5__["default"], { ...headerElement.dataset
}), headerElement);
react_dom__WEBPACK_IMPORTED_MODULE_1___default.a.render( /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_components_layout_Colophon_jsx__WEBPACK_IMPORTED_MODULE_4__["default"]), document.getElementById('colophon')); // Router
// const element = (
//     <>
//         <Router>
//             <Page />
//         </Router>
//     </>
// );
// ReactDOM.render(element, document.getElementById('root'));

/***/ }),

/***/ "./browser/src/lib/icons.js":
/*!**********************************!*\
  !*** ./browser/src/lib/icons.js ***!
  \**********************************/
/*! exports provided: QuestionBlock, LoadingMascot, default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "QuestionBlock", function() { return QuestionBlock; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "LoadingMascot", function() { return LoadingMascot; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _images_icons_questionblock_png__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../images/icons/questionblock.png */ "./browser/images/icons/questionblock.png");
/* harmony import */ var _images_icons_loading_mascot_gif__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../images/icons/loading_mascot.gif */ "./browser/images/icons/loading_mascot.gif");



function QuestionBlock({
  className: classNameProp,
  ...props
}) {
  const className = `icon ${classNameProp}`;
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement('img', { ...props,
    src: _images_icons_questionblock_png__WEBPACK_IMPORTED_MODULE_1__["default"],
    alt: '[?]',
    className
  });
}
function LoadingMascot({
  className: classNameProp,
  ...props
}) {
  const className = `icon ${classNameProp}`;
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement('img', { ...props,
    src: _images_icons_loading_mascot_gif__WEBPACK_IMPORTED_MODULE_2__["default"],
    alt: 'loading',
    className
  });
}
/* harmony default export */ __webpack_exports__["default"] = ({
  QuestionBlock,
  LoadingMascot
});

/***/ }),

/***/ "./browser/src/lib/match-component.js":
/*!********************************************!*\
  !*** ./browser/src/lib/match-component.js ***!
  \********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
const matchComponent = Component => c => {
  // React Component
  if (c.type === Component) {
    return true;
  } // Matching componentType


  if (c.props && c.props.componentType === Component) {
    return true;
  }

  return false;
};

/* harmony default export */ __webpack_exports__["default"] = (matchComponent);

/***/ }),

/***/ "./browser/src/lib/storage-available.js":
/*!**********************************************!*\
  !*** ./browser/src/lib/storage-available.js ***!
  \**********************************************/
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

/***/ "./browser/styles/app.scss":
/*!*********************************!*\
  !*** ./browser/styles/app.scss ***!
  \*********************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

/******/ });
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vd2VicGFjay9ib290c3RyYXAiLCJ3ZWJwYWNrOi8vLy4vYnJvd3Nlci9pbWFnZXMvY29sb3Bob25fd2VsY29tZS5wbmciLCJ3ZWJwYWNrOi8vLy4vYnJvd3Nlci9pbWFnZXMvaWNvbnMvbG9hZGluZ19tYXNjb3QuZ2lmIiwid2VicGFjazovLy8uL2Jyb3dzZXIvaW1hZ2VzL2ljb25zL3F1ZXN0aW9uYmxvY2sucG5nIiwid2VicGFjazovLy8uL2Jyb3dzZXIvc3JjL2NvbXBvbmVudHMvbGF5b3V0L0NvbG9waG9uLmpzeCIsIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9jb21wb25lbnRzL2xheW91dC9IZWFkZXIuanN4Iiwid2VicGFjazovLy8uL2Jyb3dzZXIvc3JjL2NvbXBvbmVudHMvbGF5b3V0L1NlYXJjaC5qc3giLCJ3ZWJwYWNrOi8vLy4vYnJvd3Nlci9zcmMvY29tcG9uZW50cy9sYXlvdXQvVG9wTmF2LmpzeCIsIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9jb21wb25lbnRzL3VpL0J1dHRvbi5qc3giLCJ3ZWJwYWNrOi8vLy4vYnJvd3Nlci9zcmMvY29tcG9uZW50cy91aS9Nb2RhbC5qc3giLCJ3ZWJwYWNrOi8vLy4vYnJvd3Nlci9zcmMvY29tcG9uZW50cy91aS9OYXZNZW51LmpzeCIsIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9pbmRleC5qcyIsIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9saWIvaWNvbnMuanMiLCJ3ZWJwYWNrOi8vLy4vYnJvd3Nlci9zcmMvbGliL21hdGNoLWNvbXBvbmVudC5qcyIsIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9saWIvc3RvcmFnZS1hdmFpbGFibGUuanMiLCJ3ZWJwYWNrOi8vLy4vYnJvd3Nlci9zdHlsZXMvYXBwLnNjc3MiXSwibmFtZXMiOlsiQ29sb3Bob24iLCJzdG9yYWdlQXZhaWxhYmxlIiwib3BlbiIsInNldE9wZW4iLCJSZWFjdCIsInVzZVN0YXRlIiwiaGFuZGxlQ2xvc2UiLCJldmVudCIsInByZXZlbnREZWZhdWx0IiwibG9jYWxTdG9yYWdlIiwic2V0SXRlbSIsImdldEl0ZW0iLCJwb3NpdGlvbiIsInpJbmRleCIsInJpZ2h0IiwiYm90dG9tIiwibGVmdCIsImNvbG9yIiwicGFkZGluZyIsIm1hcmdpbiIsInRleHRBbGlnbiIsInRvcCIsIndpZHRoIiwiaGVpZ2h0IiwiYmFja2dyb3VuZCIsIndlbGNvbWUiLCJUb3BOYXZVc2VyIiwibGF6eSIsIkxvZ2luIiwiSGVhZGVyVXNlciIsInVzZXJuYW1lIiwibG9naW5Mb2FkZWQiLCJzZXRMb2dpbkxvYWRlZCIsImxhenlsb2FkTG9naW4iLCJMb2dpbkJ1dHRvbiIsImhhbmRsZUNsaWNrIiwiSGVhZGVyIiwicHJvcHMiLCJBUElfRU5EUE9JTlQiLCJwcm9jZXNzIiwiU2VhcmNoIiwic2VhcmNoVGVybSIsInNldFNlYXJjaFRlcm0iLCJyZXN1bHRzSW5pdGlhbFN0YXRlIiwiaGl0cyIsImlzTG9hZGluZyIsImlzRXJyb3IiLCJyZXN1bHRzUmVkdWNlciIsInN0YXRlIiwiYWN0aW9uIiwidHlwZSIsInBheWxvYWQiLCJFcnJvciIsInJlc3VsdHMiLCJkaXNwYXRjaFJlc3VsdHMiLCJ1c2VSZWR1Y2VyIiwiaGFuZGxlU2VhcmNoIiwidGFyZ2V0IiwidmFsdWUiLCJ1c2VFZmZlY3QiLCJsZW5ndGgiLCJ1cmwiLCJmZXRjaCIsInRoZW4iLCJyZXNwb25zZSIsImpzb24iLCJyZXN1bHQiLCJjb2xsZWN0aW9uIiwiaXRlbXMiLCJjYXRjaCIsImlucHV0IiwiZm9jdXMiLCJTZWFyY2hSZXN1bHRzIiwibWFwIiwiaXRlbSIsInRpdGxlX3NvcnQiLCJTZWFyY2hSZXN1bHQiLCJsaW5rcyIsInBhZ2UiLCJ0aXRsZSIsIlRvcE5hdiIsInRvZ2dsZU9wZW4iLCJidXR0b25DbGFzc2VzIiwiYWN0aXZlIiwiaW5hY3RpdmUiLCJCdXR0b24iLCJjbGFzc2VzIiwic2l6ZSIsInZhcmlhbnQiLCJjaGlsZHJlbiIsImNsYXNzTmFtZSIsImNuIiwicHJvcFR5cGVzIiwiUHJvcFR5cGVzIiwib25lT2YiLCJhbnkiLCJub2RlIiwiZGVmYXVsdFByb3BzIiwiTW9kYWwiLCJjbG9zZSIsInRpbWVvdXQiLCJvdmVybGF5IiwiY2xvc2VCdXR0b24iLCJDbG9zZUJ1dHRvbiIsIk92ZXJsYXkiLCJpc05hdk1lbnVJdGVtIiwibWF0Y2hDb21wb25lbnQiLCJOYXZNZW51SXRlbSIsIk5hdk1lbnUiLCJpbml0U2VsZWN0ZWQiLCJDaGlsZHJlbiIsImZvckVhY2giLCJjaGlsZCIsImluZGV4Iiwic2VsZWN0ZWQiLCJzZXRTZWxlY3RlZCIsImNsYXNzTmFtZXMiLCJuYXZtZW51IiwiZmlyc3RWYWxpZENoaWxkIiwiY2hpbGRQcm9wcyIsImlzVmFsaWRFbGVtZW50IiwidGFiSW5kZXgiLCJjbG9uZUVsZW1lbnQiLCJzdHJpbmciLCJpc1JlcXVpcmVkIiwiY2FyZXQiLCJudW1iZXIiLCJib29sIiwiZnVuYyIsIkl0ZW0iLCJoZWFkZXJFbGVtZW50IiwiZG9jdW1lbnQiLCJnZXRFbGVtZW50QnlJZCIsIlJlYWN0RE9NIiwicmVuZGVyIiwiY3JlYXRlRWxlbWVudCIsImRhdGFzZXQiLCJRdWVzdGlvbkJsb2NrIiwiY2xhc3NOYW1lUHJvcCIsInNyYyIsInF1ZXN0aW9uYmxvY2siLCJhbHQiLCJMb2FkaW5nTWFzY290IiwibG9hZGluZ21hc2NvdCIsIkNvbXBvbmVudCIsImMiLCJjb21wb25lbnRUeXBlIiwic3RvcmFnZSIsIndpbmRvdyIsIngiLCJyZW1vdmVJdGVtIiwiZSIsIkRPTUV4Y2VwdGlvbiIsImNvZGUiLCJuYW1lIl0sIm1hcHBpbmdzIjoiO1FBQUE7UUFDQTtRQUNBO1FBQ0E7UUFDQTs7UUFFQTtRQUNBO1FBQ0E7UUFDQSxRQUFRLG9CQUFvQjtRQUM1QjtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7O1FBRUE7UUFDQTtRQUNBOztRQUVBO1FBQ0E7O1FBRUE7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBLGlCQUFpQiw0QkFBNEI7UUFDN0M7UUFDQTtRQUNBLGtCQUFrQiwyQkFBMkI7UUFDN0M7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTs7UUFFQTtRQUNBOztRQUVBO1FBQ0E7O1FBRUE7UUFDQTtRQUNBO1FBQ0E7O1FBRUE7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBOztRQUVBOztRQUVBO1FBQ0E7UUFDQSx5Q0FBeUMsOENBQThDO1FBQ3ZGOztRQUVBO1FBQ0E7O1FBRUE7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7O1FBRUE7UUFDQTs7UUFFQTtRQUNBOztRQUVBO1FBQ0E7UUFDQTs7UUFFQTtRQUNBO1FBQ0E7UUFDQTs7O1FBR0E7UUFDQSxvQkFBb0I7UUFDcEI7UUFDQTtRQUNBO1FBQ0EsdUJBQXVCLDhDQUE4QztRQUNyRTtRQUNBO1FBQ0EsbUJBQW1CLDZCQUE2QjtRQUNoRDtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0EsbUJBQW1CLDhCQUE4QjtRQUNqRDtRQUNBO1FBQ0E7UUFDQTtRQUNBOztRQUVBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBOztRQUVBO1FBQ0EsS0FBSztRQUNMO1FBQ0EsS0FBSztRQUNMOztRQUVBOztRQUVBO1FBQ0EsaUNBQWlDOztRQUVqQztRQUNBO1FBQ0E7UUFDQSxLQUFLO1FBQ0w7UUFDQTtRQUNBO1FBQ0EsTUFBTTtRQUNOOztRQUVBO1FBQ0E7UUFDQTs7UUFFQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7O1FBRUE7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0Esd0JBQXdCLGtDQUFrQztRQUMxRCxNQUFNO1FBQ047UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBOztRQUVBO1FBQ0E7O1FBRUE7UUFDQTs7UUFFQTtRQUNBO1FBQ0E7UUFDQSwwQ0FBMEMsZ0NBQWdDO1FBQzFFO1FBQ0E7O1FBRUE7UUFDQTtRQUNBO1FBQ0Esd0RBQXdELGtCQUFrQjtRQUMxRTtRQUNBLGlEQUFpRCxjQUFjO1FBQy9EOztRQUVBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQSx5Q0FBeUMsaUNBQWlDO1FBQzFFLGdIQUFnSCxtQkFBbUIsRUFBRTtRQUNySTtRQUNBOztRQUVBO1FBQ0E7UUFDQTtRQUNBLDJCQUEyQiwwQkFBMEIsRUFBRTtRQUN2RCxpQ0FBaUMsZUFBZTtRQUNoRDtRQUNBO1FBQ0E7O1FBRUE7UUFDQSxzREFBc0QsK0RBQStEOztRQUVySDtRQUNBOztRQUVBO1FBQ0EsMENBQTBDLG9CQUFvQixXQUFXOztRQUV6RTtRQUNBO1FBQ0E7UUFDQTtRQUNBLGdCQUFnQix1QkFBdUI7UUFDdkM7OztRQUdBO1FBQ0E7UUFDQTtRQUNBOzs7Ozs7Ozs7Ozs7O0FDMVFBO0FBQWUsK0VBQWdCLHd6Qjs7Ozs7Ozs7Ozs7O0FDQS9CO0FBQWUsK0VBQWdCLG9tQjs7Ozs7Ozs7Ozs7O0FDQS9CO0FBQWUsK0VBQWdCLG8zSDs7Ozs7Ozs7Ozs7O0FDQS9CO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBRUE7QUFDQTtBQUVlLFNBQVNBLFFBQVQsR0FBb0I7QUFDL0IsTUFBSSxDQUFDQyx5RUFBZ0IsQ0FBQyxjQUFELENBQXJCLEVBQXVDO0FBQ25DLFdBQU8sRUFBUDtBQUNILEdBSDhCLENBSS9CO0FBQ0E7OztBQUVBLFFBQU0sQ0FBQ0MsSUFBRCxFQUFPQyxPQUFQLElBQWtCQyw0Q0FBSyxDQUFDQyxRQUFOLENBQWUsSUFBZixDQUF4Qjs7QUFFQSxXQUFTQyxXQUFULENBQXFCQyxLQUFyQixFQUE0QjtBQUN4QkEsU0FBSyxDQUFDQyxjQUFOO0FBQ0FMLFdBQU8sQ0FBQyxLQUFELENBQVA7QUFDQU0sZ0JBQVksQ0FBQ0MsT0FBYixDQUFxQixVQUFyQixFQUFpQyxRQUFqQztBQUNIOztBQUVELE1BQUlSLElBQUksSUFBSU8sWUFBWSxDQUFDRSxPQUFiLENBQXFCLFVBQXJCLE1BQXFDLFFBQWpELEVBQTJEO0FBQ3ZELHdCQUNJO0FBQUssZUFBUyxFQUFDLDJCQUFmO0FBQTJDLFdBQUssRUFBRTtBQUFFQyxnQkFBUSxFQUFFLE9BQVo7QUFBcUJDLGNBQU0sRUFBRSxHQUE3QjtBQUFrQ0MsYUFBSyxFQUFFLENBQXpDO0FBQTRDQyxjQUFNLEVBQUUsQ0FBcEQ7QUFBdURDLFlBQUksRUFBRSxDQUE3RDtBQUFnRUMsYUFBSyxFQUFFO0FBQXZFO0FBQWxELG9CQUNJO0FBQUssV0FBSyxFQUFFO0FBQUVDLGVBQU8sRUFBRSxlQUFYO0FBQTRCQyxjQUFNLEVBQUUsUUFBcEM7QUFBOENDLGlCQUFTLEVBQUU7QUFBekQ7QUFBWixnREFFSSxzRUFGSixlQUdJO0FBQUcsVUFBSSxFQUFDLFFBQVI7QUFBaUIsV0FBSyxFQUFDLGlEQUF2QjtBQUF5RSxlQUFTLEVBQUMsU0FBbkY7QUFBNkYsYUFBTyxFQUFFZDtBQUF0RywrQ0FISixDQURKLGVBTUk7QUFBSyxXQUFLLEVBQUU7QUFBRU0sZ0JBQVEsRUFBRSxVQUFaO0FBQXdCQyxjQUFNLEVBQUUsQ0FBaEM7QUFBbUNRLFdBQUcsRUFBQyxNQUF2QztBQUErQ0wsWUFBSSxFQUFFLEtBQXJEO0FBQTRETSxhQUFLLEVBQUMsT0FBbEU7QUFBMkVDLGNBQU0sRUFBQyxNQUFsRjtBQUEwRkosY0FBTSxFQUFFLGFBQWxHO0FBQWlISyxrQkFBVSxFQUFHLE9BQU1DLG9FQUFRO0FBQTVJO0FBQVosTUFOSixlQU9JO0FBQUssV0FBSyxFQUFFO0FBQUViLGdCQUFRLEVBQUUsVUFBWjtBQUF3QkMsY0FBTSxFQUFFLENBQWhDO0FBQW1DRSxjQUFNLEVBQUMsR0FBMUM7QUFBK0NDLFlBQUksRUFBRSxLQUFyRDtBQUE0RE0sYUFBSyxFQUFDLE9BQWxFO0FBQTJFQyxjQUFNLEVBQUMsTUFBbEY7QUFBMEZKLGNBQU0sRUFBRSxhQUFsRztBQUFpSEssa0JBQVUsRUFBRyxPQUFNQyxvRUFBUTtBQUE1STtBQUFaLE1BUEosZUFRSTtBQUFLLFdBQUssRUFBRTtBQUFFYixnQkFBUSxFQUFFLFVBQVo7QUFBd0JDLGNBQU0sRUFBRSxDQUFoQztBQUFtQ0MsYUFBSyxFQUFDLEdBQXpDO0FBQThDQyxjQUFNLEVBQUUsR0FBdEQ7QUFBMkRDLFlBQUksRUFBQyxHQUFoRTtBQUFxRU0sYUFBSyxFQUFDLE1BQTNFO0FBQW1GQyxjQUFNLEVBQUUsTUFBM0Y7QUFBbUdDLGtCQUFVLEVBQUcsT0FBTUMsb0VBQVE7QUFBOUg7QUFBWixNQVJKLENBREo7QUFZSDs7QUFFRCxTQUFPLEVBQVA7QUFDSCxDOzs7Ozs7Ozs7Ozs7QUNwQ0Q7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFFQSxNQUFNQyxVQUFVLGdCQUFHdEIsNENBQUssQ0FBQ3VCLElBQU4sQ0FBVyxNQUFNLG1MQUFqQixDQUFuQjtBQUNBLE1BQU1DLEtBQUssZ0JBQUd4Qiw0Q0FBSyxDQUFDdUIsSUFBTixDQUFXLE1BQU0sMkpBQWpCLENBQWQ7O0FBRUEsU0FBU0UsVUFBVCxDQUFvQjtBQUFFQztBQUFGLENBQXBCLEVBQWtDO0FBQzlCLFFBQU0sQ0FBQ0MsV0FBRCxFQUFjQyxjQUFkLElBQWdDNUIsNENBQUssQ0FBQ0MsUUFBTixDQUFlLEtBQWYsQ0FBdEM7O0FBQ0EsUUFBTTRCLGFBQWEsR0FBSTFCLEtBQUQsSUFBVztBQUM3QkEsU0FBSyxDQUFDQyxjQUFOO0FBQ0F3QixrQkFBYyxDQUFDLElBQUQsQ0FBZDtBQUNILEdBSEQ7O0FBS0EsUUFBTUUsV0FBVyxHQUFHLENBQUM7QUFBRUM7QUFBRixHQUFELGtCQUNoQiwyREFBQyxzREFBRDtBQUFRLFNBQUssRUFBQyxPQUFkO0FBQXNCLFdBQU8sRUFBRUEsV0FBL0I7QUFBNEMsV0FBTyxFQUFFO0FBQUUsdUJBQWlCO0FBQW5CO0FBQXJELGtCQUNJLDJEQUFDLDJEQUFELE9BREosQ0FESjs7QUFNQSxzQkFDSTtBQUFLLE1BQUUsRUFBQztBQUFSLEtBQ0tMLFFBQVEsZ0JBQ0wsMkRBQUMsNENBQUQsQ0FBTyxRQUFQO0FBQWdCLFlBQVEsRUFBQztBQUF6QixrQkFBZ0MsMkRBQUMsVUFBRDtBQUFZLFlBQVEsRUFBRUE7QUFBdEIsSUFBaEMsQ0FESyxnQkFHTCx3SEFDS0MsV0FBVyxnQkFDTiwyREFBQyw0Q0FBRCxDQUFPLFFBQVA7QUFBZ0IsWUFBUSxlQUFFLDJEQUFDLDJEQUFEO0FBQTFCLGtCQUE2QywyREFBQyxLQUFEO0FBQU8sZUFBVyxFQUFFRztBQUFwQixJQUE3QyxDQURNLGdCQUVOLDJEQUFDLFdBQUQ7QUFBYSxlQUFXLEVBQUVEO0FBQTFCLElBSFYsQ0FKUixDQURKO0FBY0g7O0FBRWMsU0FBU0csTUFBVCxDQUFnQkMsS0FBaEIsRUFBdUI7QUFDbEMsUUFBTTtBQUFFUDtBQUFGLE1BQWVPLEtBQXJCO0FBRUEsc0JBQ0kscUlBQ0ksMkRBQUMsbURBQUQsT0FESixlQUVJLDJEQUFDLFVBQUQ7QUFBWSxZQUFRLEVBQUVQO0FBQXRCLElBRkosZUFHSSwyREFBQyxtREFBRCxPQUhKLENBREo7QUFPSCxDOzs7Ozs7Ozs7Ozs7QUNoREQ7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBRUEsTUFBTVEsWUFBWSxHQUFHQyxnQkFBckI7QUFFZSxTQUFTQyxNQUFULEdBQWtCO0FBQzdCLFFBQU0sQ0FBQ0MsVUFBRCxFQUFhQyxhQUFiLElBQThCdEMsNENBQUssQ0FBQ0MsUUFBTixDQUFlLEVBQWYsQ0FBcEM7QUFFQSxRQUFNc0MsbUJBQW1CLEdBQUc7QUFDeEJDLFFBQUksRUFBRSxFQURrQjtBQUV4QkMsYUFBUyxFQUFFLEtBRmE7QUFHeEJDLFdBQU8sRUFBRTtBQUhlLEdBQTVCOztBQUtBLFFBQU1DLGNBQWMsR0FBRyxDQUFDQyxLQUFELEVBQVFDLE1BQVIsS0FBbUI7QUFDdEMsWUFBUUEsTUFBTSxDQUFDQyxJQUFmO0FBQ0ksV0FBSyxtQkFBTDtBQUNJLGVBQU8sRUFDSCxHQUFHRixLQURBO0FBRUhILG1CQUFTLEVBQUUsSUFGUjtBQUdIQyxpQkFBTyxFQUFFO0FBSE4sU0FBUDs7QUFLSixXQUFLLHNCQUFMO0FBQ0ksZUFBTyxFQUNILEdBQUdFLEtBREE7QUFFSEgsbUJBQVMsRUFBRSxLQUZSO0FBR0hDLGlCQUFPLEVBQUUsS0FITjtBQUlIRixjQUFJLEVBQUVLLE1BQU0sQ0FBQ0U7QUFKVixTQUFQOztBQU1KLFdBQUssbUJBQUw7QUFDSSxlQUFPLEVBQ0gsR0FBR0gsS0FEQTtBQUVISCxtQkFBUyxFQUFFLEtBRlI7QUFHSEMsaUJBQU8sRUFBRTtBQUhOLFNBQVA7O0FBS0osV0FBSyxPQUFMO0FBQ0ksZUFBTyxFQUNILEdBQUdFLEtBREE7QUFFSEgsbUJBQVMsRUFBRSxLQUZSO0FBR0hDLGlCQUFPLEVBQUUsS0FITjtBQUlIRixjQUFJLEVBQUU7QUFKSCxTQUFQOztBQU1KO0FBQ0ksY0FBTSxJQUFJUSxLQUFKLEVBQU47QUE1QlI7QUE4QkgsR0EvQkQsQ0FSNkIsQ0F3QzdCOzs7QUFDQSxRQUFNLENBQUNDLE9BQUQsRUFBVUMsZUFBVixJQUE2QmxELDRDQUFLLENBQUNtRCxVQUFOLENBQWlCUixjQUFqQixFQUFpQ0osbUJBQWpDLENBQW5DOztBQUVBLFFBQU1hLFlBQVksR0FBSWpELEtBQUQsSUFBVztBQUM1Qm1DLGlCQUFhLENBQUNuQyxLQUFLLENBQUNrRCxNQUFOLENBQWFDLEtBQWQsQ0FBYjtBQUNILEdBRkQ7O0FBSUF0RCw4Q0FBSyxDQUFDdUQsU0FBTixDQUFnQixNQUFNO0FBQ2xCLFFBQUksQ0FBQ2xCLFVBQUwsRUFBaUI7QUFDYmEscUJBQWUsQ0FBQztBQUFFSixZQUFJLEVBQUU7QUFBUixPQUFELENBQWY7QUFDQTtBQUNIOztBQUVELFFBQUlULFVBQVUsQ0FBQ21CLE1BQVgsR0FBb0IsQ0FBeEIsRUFBMkI7QUFDdkI7QUFDSCxLQVJpQixDQVVsQjs7O0FBQ0FOLG1CQUFlLENBQUM7QUFBRUosVUFBSSxFQUFFO0FBQVIsS0FBRCxDQUFmLENBWGtCLENBYWxCOztBQUNBLFVBQU1XLEdBQUcsR0FBR3ZCLFlBQVksR0FBR0csVUFBM0I7QUFDQXFCLFNBQUssQ0FBQ0QsR0FBRCxDQUFMLENBQ0tFLElBREwsQ0FDV0MsUUFBRCxJQUFjQSxRQUFRLENBQUNDLElBQVQsRUFEeEIsRUFFS0YsSUFGTCxDQUVXRyxNQUFELElBQVk7QUFDZFoscUJBQWUsQ0FBQztBQUNaSixZQUFJLEVBQUUsc0JBRE07QUFFWkMsZUFBTyxFQUFFZSxNQUFNLENBQUNDLFVBQVAsQ0FBa0JDO0FBRmYsT0FBRCxDQUFmO0FBSUgsS0FQTCxFQVFLQyxLQVJMLENBUVcsTUFBTWYsZUFBZSxDQUFDO0FBQUVKLFVBQUksRUFBRTtBQUFSLEtBQUQsQ0FSaEM7QUFTSCxHQXhCRCxFQXdCRyxDQUFDVCxVQUFELENBeEJIO0FBMEJBLFFBQU0sQ0FBQ3ZDLElBQUQsRUFBT0MsT0FBUCxJQUFrQkMsNENBQUssQ0FBQ0MsUUFBTixDQUFlLEtBQWYsQ0FBeEI7O0FBQ0EsUUFBTUMsV0FBVyxHQUFHLE1BQU07QUFDdEJILFdBQU8sQ0FBQyxLQUFELENBQVA7QUFDSCxHQUZEOztBQUlBLHNCQUNJO0FBQUssTUFBRSxFQUFDO0FBQVIsa0JBQ0ksMkRBQUMsc0RBQUQ7QUFBUSxXQUFPLEVBQUU7QUFBRSx1QkFBaUI7QUFBbkIsS0FBakI7QUFBNEMsV0FBTyxFQUFFLE1BQU1BLE9BQU8sQ0FBQyxJQUFEO0FBQWxFLGtCQUNJLDJEQUFDLHVEQUFEO0FBQVUsUUFBSSxFQUFDLElBQWY7QUFBb0IsU0FBSyxFQUFDO0FBQTFCLElBREosQ0FESixlQUlJLDJEQUFDLHFEQUFEO0FBQU8sUUFBSSxFQUFFRCxJQUFiO0FBQW1CLFNBQUssRUFBRUksV0FBMUI7QUFBdUMsaUJBQWEsRUFBQztBQUFyRCxrQkFDSSxxRkFDSTtBQUFPLE1BQUUsRUFBQyxZQUFWO0FBQXVCLFFBQUksRUFBQyxNQUE1QjtBQUFtQyxTQUFLLEVBQUVtQyxVQUExQztBQUFzRCxlQUFXLEVBQUMsdUJBQWxFO0FBQTBGLFlBQVEsRUFBQyxHQUFuRztBQUF1RyxZQUFRLEVBQUVlLFlBQWpIO0FBQStILE9BQUcsRUFBR2MsS0FBRCxJQUFXQSxLQUFLLElBQUlBLEtBQUssQ0FBQ0MsS0FBTjtBQUF4SixJQURKLEVBRUssR0FGTCxlQUdJO0FBQVEsUUFBSSxFQUFDLE9BQWI7QUFBcUIsWUFBUSxFQUFDLEdBQTlCO0FBQWtDLFdBQU8sRUFBRSxNQUFNN0IsYUFBYSxDQUFDLEVBQUQ7QUFBOUQsYUFISixFQUtLVyxPQUFPLENBQUNQLE9BQVIsaUJBQW1CLDZGQUx4QixFQU9LTyxPQUFPLENBQUNSLFNBQVIsZ0JBQXFCLG1GQUFyQixnQkFBMkMsMkRBQUMsYUFBRDtBQUFlLFdBQU8sRUFBRVE7QUFBeEIsSUFQaEQsQ0FESixDQUpKLENBREo7QUFrQkg7O0FBRUQsU0FBU21CLGFBQVQsQ0FBdUJuQyxLQUF2QixFQUE4QjtBQUMxQixRQUFNO0FBQUVnQjtBQUFGLE1BQWNoQixLQUFwQjs7QUFFQSxNQUFJZ0IsT0FBTyxDQUFDVCxJQUFSLENBQWFnQixNQUFiLEtBQXdCLENBQTVCLEVBQStCO0FBQzNCLHdCQUFPLHlGQUFQO0FBQ0g7O0FBRUQsc0JBQ0ksdUVBQ0tQLE9BQU8sQ0FBQ1QsSUFBUixDQUFhNkIsR0FBYixDQUFrQkMsSUFBRCxpQkFBVSwyREFBQyxZQUFEO0FBQWMsT0FBRyxFQUFFQSxJQUFJLENBQUNDLFVBQXhCO0FBQW9DLFFBQUksRUFBRUQ7QUFBMUMsSUFBM0IsQ0FETCxDQURKO0FBS0g7QUFFRDs7Ozs7OztBQUtBLFNBQVNFLFlBQVQsQ0FBc0J2QyxLQUF0QixFQUE2QjtBQUN6QixRQUFNO0FBQUVxQztBQUFGLE1BQVdyQyxLQUFqQjtBQUVBLHNCQUNJLG9GQUNJO0FBQUcsUUFBSSxFQUFFcUMsSUFBSSxDQUFDRyxLQUFMLENBQVdDO0FBQXBCLGtCQUNJLHdFQUFNSixJQUFJLENBQUNLLEtBQVgsQ0FESixFQUVLLEdBRkwsZUFHSSw4RUFFS0wsSUFBSSxDQUFDeEIsSUFGVixNQUhKLENBREosQ0FESjtBQWFILEMsQ0FFRDtBQUNBO0FBQ0E7QUFDQSxLOzs7Ozs7Ozs7Ozs7QUNsSkE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUVlLFNBQVM4QixNQUFULEdBQWtCO0FBQzdCLFFBQU0sQ0FBQzlFLElBQUQsRUFBT0MsT0FBUCxJQUFrQkMsNENBQUssQ0FBQ0MsUUFBTixDQUFlLEtBQWYsQ0FBeEI7O0FBQ0EsUUFBTTRFLFVBQVUsR0FBRyxNQUFNO0FBQ3JCOUUsV0FBTyxDQUFDLENBQUNELElBQUYsQ0FBUDtBQUNILEdBRkQ7O0FBSUEsUUFBTWdGLGFBQWEsR0FBRztBQUNsQkMsVUFBTSxFQUFFakYsSUFEVTtBQUVsQmtGLFlBQVEsRUFBRSxDQUFDbEY7QUFGTyxHQUF0QjtBQUtBLHNCQUNJLDJEQUFDLG9FQUFEO0FBQWUsTUFBRSxFQUFFQSxJQUFuQjtBQUF5QixXQUFPLEVBQUU7QUFBbEMsa0JBQ0ksMkRBQUMsdURBQUQscUJBQ0ksMkRBQUMsdURBQUQsQ0FBUyxJQUFUO0FBQWMsWUFBUTtBQUF0QixrQkFDSSxvRkFBSTtBQUFHLFFBQUksRUFBQztBQUFSLGtCQUFKLENBREosQ0FESixlQUlJLDJEQUFDLHVEQUFELENBQVMsSUFBVCxxQkFFSSwyREFBQyxzREFBRDtBQUFRLFFBQUksRUFBQyxRQUFiO0FBQXNCLG9CQUFjQSxJQUFwQztBQUEwQyxNQUFFLEVBQUMsTUFBN0M7QUFBb0QsV0FBTyxFQUFFZ0YsYUFBN0Q7QUFBNEUsV0FBTyxFQUFFRDtBQUFyRixlQUZKLENBSkosZUFVSSwyREFBQyx1REFBRCxDQUFTLElBQVQ7QUFBYyxhQUFTLEVBQUM7QUFBeEIsa0JBQWlDO0FBQUcsUUFBSSxFQUFDO0FBQVIsYUFBakMsQ0FWSixlQVdJLDJEQUFDLHVEQUFELENBQVMsSUFBVDtBQUFjLGFBQVMsRUFBQztBQUF4QixrQkFBaUM7QUFBRyxRQUFJLEVBQUM7QUFBUixjQUFqQyxDQVhKLGVBWUksMkRBQUMsdURBQUQsQ0FBUyxJQUFUO0FBQWMsYUFBUyxFQUFDO0FBQXhCLGtCQUFpQztBQUFHLFFBQUksRUFBQztBQUFSLGFBQWpDLENBWkosQ0FESixDQURKO0FBa0JILEM7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7OztBQ2xDRDtBQUNBO0FBQ0E7O0FBRUEsU0FBU0ksTUFBVCxDQUFnQjtBQUFFcEUsT0FBRjtBQUFTcUUsU0FBVDtBQUFrQkMsTUFBbEI7QUFBd0JyQyxNQUF4QjtBQUE4QnNDLFNBQTlCO0FBQXVDQyxVQUF2QztBQUFpRCxLQUFHcEQ7QUFBcEQsQ0FBaEIsRUFBNkU7QUFDekUsUUFBTXFELFNBQVMsR0FBR0MsaURBQUUsQ0FBQyxFQUNqQixHQUFHTCxPQURjO0FBRWpCLEtBQUUsZ0JBQWVyRSxLQUFNLEVBQXZCLEdBQTJCQSxLQUZWO0FBR2pCLEtBQUUsZUFBY3NFLElBQUssRUFBckIsR0FBeUJBLElBSFI7QUFJakIsS0FBRSxVQUFTQyxPQUFRLEVBQW5CLEdBQXVCQTtBQUpOLEdBQUQsQ0FBcEI7QUFPQSxzQkFDSTtBQUFRLGFBQVMsRUFBRUUsU0FBbkI7QUFBOEIsUUFBSSxFQUFFeEM7QUFBcEMsS0FBOENiLEtBQTlDLEdBQ0tvRCxRQURMLENBREo7QUFLSDs7QUFFREosTUFBTSxDQUFDTyxTQUFQLEdBQW1CO0FBQ2YzRSxPQUFLLEVBQUU0RSxpREFBUyxDQUFDQyxLQUFWLENBQWdCLENBQUMsU0FBRCxFQUFZLFNBQVosRUFBdUIsS0FBdkIsRUFBOEIsT0FBOUIsRUFBdUMsTUFBdkMsRUFBK0MsT0FBL0MsQ0FBaEIsQ0FEUTtBQUVmUixTQUFPLEVBQUVPLGlEQUFTLENBQUNFLEdBRko7QUFHZlIsTUFBSSxFQUFFTSxpREFBUyxDQUFDQyxLQUFWLENBQWdCLENBQUMsT0FBRCxFQUFVLFFBQVYsRUFBb0IsT0FBcEIsQ0FBaEIsQ0FIUztBQUlmNUMsTUFBSSxFQUFFMkMsaURBQVMsQ0FBQ0MsS0FBVixDQUFnQixDQUFDLFFBQUQsRUFBVyxRQUFYLEVBQXFCLE9BQXJCLENBQWhCLENBSlM7QUFLZk4sU0FBTyxFQUFFSyxpREFBUyxDQUFDQyxLQUFWLENBQWdCLENBQUMsTUFBRCxFQUFTLFdBQVQsRUFBc0IsVUFBdEIsRUFBa0MsTUFBbEMsRUFBMEMsT0FBMUMsQ0FBaEIsQ0FMTTtBQU1mTCxVQUFRLEVBQUVJLGlEQUFTLENBQUNHO0FBTkwsQ0FBbkI7QUFTQVgsTUFBTSxDQUFDWSxZQUFQLEdBQXNCO0FBQ2xCL0MsTUFBSSxFQUFFLFFBRFk7QUFFbEJzQyxTQUFPLEVBQUU7QUFGUyxDQUF0QjtBQUtlSCxxRUFBZixFOzs7Ozs7Ozs7Ozs7QUNqQ0E7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFDQTtBQUNBO0FBRWUsU0FBU2EsS0FBVCxDQUFlO0FBQzFCVCxVQUQwQjtBQUUxQnZGLE1BQUksR0FBRyxJQUZtQjtBQUcxQmlHLE9BQUssR0FBRyxJQUhrQjtBQUkxQkMsU0FBTyxHQUFHLEdBSmdCO0FBSzFCQyxTQUFPLEdBQUcsSUFMZ0I7QUFNMUJDLGFBQVcsR0FBRztBQU5ZLENBQWYsRUFPWjtBQUNDLFFBQU1DLFdBQVcsR0FBRyxNQUFNRCxXQUFXLGlCQUNqQztBQUFRLFFBQUksRUFBQyxRQUFiO0FBQXNCLFFBQUksRUFBQyxRQUEzQjtBQUFvQyxvQkFBY3BHLElBQWxEO0FBQXdELGtCQUFXLE9BQW5FO0FBQTJFLGFBQVMsRUFBQywwQkFBckY7QUFBZ0gsV0FBTyxFQUFFaUc7QUFBekgsa0JBQ0ksMkRBQUMsa0RBQUQ7QUFBSyxvQkFBYTtBQUFsQixJQURKLENBREo7O0FBS0EsUUFBTUssT0FBTyxHQUFHLE1BQU1ILE9BQU8saUJBQ3pCO0FBQUssYUFBUyxFQUFDLGVBQWY7QUFBK0IsUUFBSSxFQUFDLFFBQXBDO0FBQTZDLFdBQU8sRUFBRUYsS0FBdEQ7QUFBNkQsbUJBQVksTUFBekU7QUFBZ0Ysa0JBQVc7QUFBM0YsSUFESjs7QUFJQSxzQkFDSSwyREFBQyxvRUFBRDtBQUFlLE1BQUUsRUFBRWpHLElBQW5CO0FBQXlCLFdBQU8sRUFBRWtHLE9BQWxDO0FBQTJDLGNBQVUsRUFBQyxPQUF0RDtBQUE4RCxpQkFBYTtBQUEzRSxrQkFDSTtBQUFLLGFBQVMsRUFBQztBQUFmLGtCQUNJLDJEQUFDLE9BQUQsT0FESixlQUVJO0FBQUssYUFBUyxFQUFDO0FBQWYsS0FDS1gsUUFETCxlQUVJLDJEQUFDLFdBQUQsT0FGSixDQUZKLENBREosQ0FESjtBQVdILEM7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7QUNoQ0Q7QUFDQTtBQUNBO0FBQ0E7QUFFQSxNQUFNZ0IsYUFBYSxHQUFHQyx1RUFBYyxDQUFDQyxXQUFELENBQXBDO0FBRUE7Ozs7O0FBSUEsU0FBU0MsT0FBVCxDQUFpQjtBQUFFbEIsV0FBRjtBQUFhRCxVQUFiO0FBQXVCLEtBQUdwRDtBQUExQixDQUFqQixFQUFvRDtBQUNoRCxNQUFJd0UsWUFBSjtBQUNBekcsOENBQUssQ0FBQzBHLFFBQU4sQ0FBZUMsT0FBZixDQUF1QnRCLFFBQXZCLEVBQWlDLENBQUN1QixLQUFELEVBQVFDLEtBQVIsS0FBa0I7QUFDL0MsUUFBSUQsS0FBSyxDQUFDM0UsS0FBTixDQUFZNkUsUUFBaEIsRUFBMEI7QUFDdEJMLGtCQUFZLEdBQUdJLEtBQWY7QUFDSDtBQUNKLEdBSkQsRUFGZ0QsQ0FRaEQ7O0FBQ0EsUUFBTSxDQUFDQyxRQUFELEVBQVdDLFdBQVgsSUFBMEIvRyw0Q0FBSyxDQUFDQyxRQUFOLENBQWV3RyxZQUFmLENBQWhDO0FBRUEsUUFBTU8sVUFBVSxHQUFHekIsaURBQUUsQ0FBQ0QsU0FBRCxFQUFZO0FBQzdCMkIsV0FBTyxFQUFFO0FBRG9CLEdBQVosQ0FBckIsQ0FYZ0QsQ0FlaEQ7O0FBQ0EsTUFBSUMsZUFBSjtBQUNBLE1BQUlDLFVBQUo7QUFFQSxzQkFDSTtBQUFLLGFBQVMsRUFBRUg7QUFBaEIsS0FBZ0MvRSxLQUFoQyxHQUNLakMsNENBQUssQ0FBQzBHLFFBQU4sQ0FBZXJDLEdBQWYsQ0FBbUJnQixRQUFuQixFQUE2QixDQUFDdUIsS0FBRCxFQUFRQyxLQUFSLEtBQWtCO0FBQzVDLFFBQUksZUFBQzdHLDRDQUFLLENBQUNvSCxjQUFOLENBQXFCUixLQUFyQixDQUFMLEVBQWtDO0FBQzlCLGFBQU9BLEtBQVA7QUFDSDs7QUFFRCxRQUFJUCxhQUFhLENBQUNPLEtBQUQsQ0FBakIsRUFBMEI7QUFDdEJNLHFCQUFlLEdBQUdBLGVBQWUsSUFBSUwsS0FBckM7QUFDQU0sZ0JBQVUsR0FBRyxFQUNULEdBQUdQLEtBQUssQ0FBQzNFLEtBREE7QUFFVDRFLGFBRlM7QUFHVEUsbUJBSFM7QUFJVEQsZ0JBQVEsRUFBRUEsUUFBUSxLQUFLRCxLQUpkO0FBS1RRLGdCQUFRLEVBQUVILGVBQWUsS0FBS0wsS0FBcEIsR0FBNEIsQ0FBNUIsR0FBZ0MsQ0FBQztBQUxsQyxPQUFiO0FBUUEsMEJBQU83Ryw0Q0FBSyxDQUFDc0gsWUFBTixDQUFtQlYsS0FBbkIsRUFBMEJPLFVBQTFCLENBQVA7QUFDSDs7QUFFRCxXQUFPUCxLQUFQO0FBQ0gsR0FuQkEsQ0FETCxDQURKO0FBd0JIOztBQUNESixPQUFPLENBQUNoQixTQUFSLEdBQW9CO0FBQ2hCRixXQUFTLEVBQUVHLGlEQUFTLENBQUM4QixNQURMO0FBRWhCbEMsVUFBUSxFQUFFSSxpREFBUyxDQUFDRyxJQUFWLENBQWU0QjtBQUZULENBQXBCO0FBSUFoQixPQUFPLENBQUNYLFlBQVIsR0FBdUI7QUFDbkJQLFdBQVMsRUFBRTtBQURRLENBQXZCOztBQUlBLFNBQVNpQixXQUFULENBQXFCO0FBQ2pCTSxPQURpQjtBQUVqQlksT0FGaUI7QUFHakJYLFVBSGlCO0FBSWpCQyxhQUppQjtBQUtqQnpCLFdBTGlCO0FBTWpCRCxVQU5pQjtBQU9qQixLQUFHcEQ7QUFQYyxDQUFyQixFQVFHO0FBQ0MsUUFBTUYsV0FBVyxHQUFJNUIsS0FBRCxJQUFXO0FBQzNCNEcsZUFBVyxDQUFDRixLQUFELENBQVg7QUFDSCxHQUZEOztBQUlBLFFBQU1HLFVBQVUsR0FBR3pCLGlEQUFFLENBQUNELFNBQUQsRUFBWTtBQUM3QixvQkFBZ0IsSUFEYTtBQUU3QndCO0FBRjZCLEdBQVosQ0FBckI7QUFLQSxzQkFDSTtBQUFLLGFBQVMsRUFBRUUsVUFBaEI7QUFBNEIsUUFBSSxFQUFDLFVBQWpDO0FBQTRDLFdBQU8sRUFBRWpGLFdBQXJEO0FBQWtFLG1CQUFZO0FBQTlFLEtBQXlGRSxLQUF6RixHQUNLd0YsS0FBSyxpQkFBSTtBQUFLLGFBQVMsRUFBQztBQUFmLGFBRGQsZUFFSTtBQUFLLGFBQVMsRUFBQztBQUFmLEtBQXVDcEMsUUFBdkMsQ0FGSixDQURKO0FBTUg7O0FBQ0RrQixXQUFXLENBQUNmLFNBQVosR0FBd0I7QUFDcEJxQixPQUFLLEVBQUVwQixpREFBUyxDQUFDaUMsTUFERzs7QUFFcEI7QUFDQUQsT0FBSyxFQUFFaEMsaURBQVMsQ0FBQ2tDLElBSEc7O0FBSXBCO0FBQ0FiLFVBQVEsRUFBRXJCLGlEQUFTLENBQUNrQyxJQUxBO0FBTXBCWixhQUFXLEVBQUV0QixpREFBUyxDQUFDbUMsSUFOSDtBQU9wQnRDLFdBQVMsRUFBRUcsaURBQVMsQ0FBQzhCLE1BUEQ7QUFRcEJsQyxVQUFRLEVBQUVJLGlEQUFTLENBQUNHO0FBUkEsQ0FBeEI7QUFVQVcsV0FBVyxDQUFDVixZQUFaLEdBQTJCO0FBQ3ZCNEIsT0FBSyxFQUFFLElBRGdCO0FBRXZCWCxVQUFRLEVBQUUsS0FGYTtBQUd2QnhCLFdBQVMsRUFBRTtBQUhZLENBQTNCO0FBTUFrQixPQUFPLENBQUNxQixJQUFSLEdBQWV0QixXQUFmO0FBRWVDLHNFQUFmLEU7Ozs7Ozs7Ozs7OztBQzFHQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUVBO0NBR0E7O0FBQ0E7Q0FHQTs7QUFDQTtDQUdBOztBQUNBLE1BQU1zQixhQUFhLEdBQUdDLFFBQVEsQ0FBQ0MsY0FBVCxDQUF3QixRQUF4QixDQUF0QjtBQUNBQyxnREFBUSxDQUFDQyxNQUFULGVBQWdCbEksNENBQUssQ0FBQ21JLGFBQU4sQ0FBb0JuRyxxRUFBcEIsRUFBNEIsRUFBQyxHQUFHOEYsYUFBYSxDQUFDTTtBQUFsQixDQUE1QixDQUFoQixFQUF5RU4sYUFBekU7QUFFQUcsZ0RBQVEsQ0FBQ0MsTUFBVCxlQUNJbEksNENBQUssQ0FBQ21JLGFBQU4sQ0FBb0J2SSx1RUFBcEIsQ0FESixFQUVJbUksUUFBUSxDQUFDQyxjQUFULENBQXdCLFVBQXhCLENBRkosRSxDQUtBO0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFFQSw2RDs7Ozs7Ozs7Ozs7O0FDaENBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFFQTtBQUNBO0FBRU8sU0FBU0ssYUFBVCxDQUF1QjtBQUFFL0MsV0FBUyxFQUFFZ0QsYUFBYjtBQUE0QixLQUFHckc7QUFBL0IsQ0FBdkIsRUFBK0Q7QUFDbEUsUUFBTXFELFNBQVMsR0FBSSxRQUFPZ0QsYUFBYyxFQUF4QztBQUNBLHNCQUFPdEksNENBQUssQ0FBQ21JLGFBQU4sQ0FBb0IsS0FBcEIsRUFBMkIsRUFDOUIsR0FBR2xHLEtBRDJCO0FBQ3BCc0csT0FBRyxFQUFFQyx1RUFEZTtBQUNBQyxPQUFHLEVBQUUsS0FETDtBQUNZbkQ7QUFEWixHQUEzQixDQUFQO0FBR0g7QUFFTSxTQUFTb0QsYUFBVCxDQUF1QjtBQUFFcEQsV0FBUyxFQUFFZ0QsYUFBYjtBQUE0QixLQUFHckc7QUFBL0IsQ0FBdkIsRUFBK0Q7QUFDbEUsUUFBTXFELFNBQVMsR0FBSSxRQUFPZ0QsYUFBYyxFQUF4QztBQUNBLHNCQUFPdEksNENBQUssQ0FBQ21JLGFBQU4sQ0FBb0IsS0FBcEIsRUFBMkIsRUFDOUIsR0FBR2xHLEtBRDJCO0FBQ3BCc0csT0FBRyxFQUFFSSx3RUFEZTtBQUNBRixPQUFHLEVBQUUsU0FETDtBQUNnQm5EO0FBRGhCLEdBQTNCLENBQVA7QUFHSDtBQUVjO0FBQUUrQyxlQUFGO0FBQWlCSztBQUFqQixDQUFmLEU7Ozs7Ozs7Ozs7OztBQ25CQTtBQUFBLE1BQU1wQyxjQUFjLEdBQUlzQyxTQUFELElBQWdCQyxDQUFELElBQU87QUFDekM7QUFDQSxNQUFJQSxDQUFDLENBQUMvRixJQUFGLEtBQVc4RixTQUFmLEVBQTBCO0FBQ3RCLFdBQU8sSUFBUDtBQUNILEdBSndDLENBTXpDOzs7QUFDQSxNQUFJQyxDQUFDLENBQUM1RyxLQUFGLElBQVc0RyxDQUFDLENBQUM1RyxLQUFGLENBQVE2RyxhQUFSLEtBQTBCRixTQUF6QyxFQUFvRDtBQUNoRCxXQUFPLElBQVA7QUFDSDs7QUFFRCxTQUFPLEtBQVA7QUFDSCxDQVpEOztBQWNldEMsNkVBQWYsRTs7Ozs7Ozs7Ozs7O0FDZEE7QUFBQTtBQUFBO0FBQ0E7QUFFZSxTQUFTekcsZ0JBQVQsQ0FBMEJpRCxJQUExQixFQUFnQztBQUMzQyxNQUFJaUcsT0FBSjs7QUFDQSxNQUFJO0FBQ0FBLFdBQU8sR0FBR0MsTUFBTSxDQUFDbEcsSUFBRCxDQUFoQjtBQUNBLFVBQU1tRyxDQUFDLEdBQUcsa0JBQVY7QUFDQUYsV0FBTyxDQUFDekksT0FBUixDQUFnQjJJLENBQWhCLEVBQW1CQSxDQUFuQjtBQUNBRixXQUFPLENBQUNHLFVBQVIsQ0FBbUJELENBQW5CO0FBRUEsV0FBTyxJQUFQO0FBQ0gsR0FQRCxDQU9FLE9BQU9FLENBQVAsRUFBVTtBQUNSLFdBQU9BLENBQUMsWUFBWUMsWUFBYixNQUNIO0FBQ0FELEtBQUMsQ0FBQ0UsSUFBRixLQUFXLEVBQVgsQ0FDQTtBQURBLE9BRUdGLENBQUMsQ0FBQ0UsSUFBRixLQUFXLElBRmQsQ0FHQTtBQUNBO0FBSkEsT0FLR0YsQ0FBQyxDQUFDRyxJQUFGLEtBQVcsb0JBTGQsQ0FNQTtBQU5BLE9BT0dILENBQUMsQ0FBQ0csSUFBRixLQUFXLDRCQVRYLEVBVUg7QUFWRyxPQVdDUCxPQUFPLElBQUlBLE9BQU8sQ0FBQ3ZGLE1BQVIsS0FBbUIsQ0FYdEM7QUFZSDtBQUNKLEM7Ozs7Ozs7Ozs7OztBQzFCRDtBQUFBIiwiZmlsZSI6ImluZGV4LmJ1bmRsZS5qcyIsInNvdXJjZXNDb250ZW50IjpbIiBcdC8vIGluc3RhbGwgYSBKU09OUCBjYWxsYmFjayBmb3IgY2h1bmsgbG9hZGluZ1xuIFx0ZnVuY3Rpb24gd2VicGFja0pzb25wQ2FsbGJhY2soZGF0YSkge1xuIFx0XHR2YXIgY2h1bmtJZHMgPSBkYXRhWzBdO1xuIFx0XHR2YXIgbW9yZU1vZHVsZXMgPSBkYXRhWzFdO1xuIFx0XHR2YXIgZXhlY3V0ZU1vZHVsZXMgPSBkYXRhWzJdO1xuXG4gXHRcdC8vIGFkZCBcIm1vcmVNb2R1bGVzXCIgdG8gdGhlIG1vZHVsZXMgb2JqZWN0LFxuIFx0XHQvLyB0aGVuIGZsYWcgYWxsIFwiY2h1bmtJZHNcIiBhcyBsb2FkZWQgYW5kIGZpcmUgY2FsbGJhY2tcbiBcdFx0dmFyIG1vZHVsZUlkLCBjaHVua0lkLCBpID0gMCwgcmVzb2x2ZXMgPSBbXTtcbiBcdFx0Zm9yKDtpIDwgY2h1bmtJZHMubGVuZ3RoOyBpKyspIHtcbiBcdFx0XHRjaHVua0lkID0gY2h1bmtJZHNbaV07XG4gXHRcdFx0aWYoT2JqZWN0LnByb3RvdHlwZS5oYXNPd25Qcm9wZXJ0eS5jYWxsKGluc3RhbGxlZENodW5rcywgY2h1bmtJZCkgJiYgaW5zdGFsbGVkQ2h1bmtzW2NodW5rSWRdKSB7XG4gXHRcdFx0XHRyZXNvbHZlcy5wdXNoKGluc3RhbGxlZENodW5rc1tjaHVua0lkXVswXSk7XG4gXHRcdFx0fVxuIFx0XHRcdGluc3RhbGxlZENodW5rc1tjaHVua0lkXSA9IDA7XG4gXHRcdH1cbiBcdFx0Zm9yKG1vZHVsZUlkIGluIG1vcmVNb2R1bGVzKSB7XG4gXHRcdFx0aWYoT2JqZWN0LnByb3RvdHlwZS5oYXNPd25Qcm9wZXJ0eS5jYWxsKG1vcmVNb2R1bGVzLCBtb2R1bGVJZCkpIHtcbiBcdFx0XHRcdG1vZHVsZXNbbW9kdWxlSWRdID0gbW9yZU1vZHVsZXNbbW9kdWxlSWRdO1xuIFx0XHRcdH1cbiBcdFx0fVxuIFx0XHRpZihwYXJlbnRKc29ucEZ1bmN0aW9uKSBwYXJlbnRKc29ucEZ1bmN0aW9uKGRhdGEpO1xuXG4gXHRcdHdoaWxlKHJlc29sdmVzLmxlbmd0aCkge1xuIFx0XHRcdHJlc29sdmVzLnNoaWZ0KCkoKTtcbiBcdFx0fVxuXG4gXHRcdC8vIGFkZCBlbnRyeSBtb2R1bGVzIGZyb20gbG9hZGVkIGNodW5rIHRvIGRlZmVycmVkIGxpc3RcbiBcdFx0ZGVmZXJyZWRNb2R1bGVzLnB1c2guYXBwbHkoZGVmZXJyZWRNb2R1bGVzLCBleGVjdXRlTW9kdWxlcyB8fCBbXSk7XG5cbiBcdFx0Ly8gcnVuIGRlZmVycmVkIG1vZHVsZXMgd2hlbiBhbGwgY2h1bmtzIHJlYWR5XG4gXHRcdHJldHVybiBjaGVja0RlZmVycmVkTW9kdWxlcygpO1xuIFx0fTtcbiBcdGZ1bmN0aW9uIGNoZWNrRGVmZXJyZWRNb2R1bGVzKCkge1xuIFx0XHR2YXIgcmVzdWx0O1xuIFx0XHRmb3IodmFyIGkgPSAwOyBpIDwgZGVmZXJyZWRNb2R1bGVzLmxlbmd0aDsgaSsrKSB7XG4gXHRcdFx0dmFyIGRlZmVycmVkTW9kdWxlID0gZGVmZXJyZWRNb2R1bGVzW2ldO1xuIFx0XHRcdHZhciBmdWxmaWxsZWQgPSB0cnVlO1xuIFx0XHRcdGZvcih2YXIgaiA9IDE7IGogPCBkZWZlcnJlZE1vZHVsZS5sZW5ndGg7IGorKykge1xuIFx0XHRcdFx0dmFyIGRlcElkID0gZGVmZXJyZWRNb2R1bGVbal07XG4gXHRcdFx0XHRpZihpbnN0YWxsZWRDaHVua3NbZGVwSWRdICE9PSAwKSBmdWxmaWxsZWQgPSBmYWxzZTtcbiBcdFx0XHR9XG4gXHRcdFx0aWYoZnVsZmlsbGVkKSB7XG4gXHRcdFx0XHRkZWZlcnJlZE1vZHVsZXMuc3BsaWNlKGktLSwgMSk7XG4gXHRcdFx0XHRyZXN1bHQgPSBfX3dlYnBhY2tfcmVxdWlyZV9fKF9fd2VicGFja19yZXF1aXJlX18ucyA9IGRlZmVycmVkTW9kdWxlWzBdKTtcbiBcdFx0XHR9XG4gXHRcdH1cblxuIFx0XHRyZXR1cm4gcmVzdWx0O1xuIFx0fVxuXG4gXHQvLyBUaGUgbW9kdWxlIGNhY2hlXG4gXHR2YXIgaW5zdGFsbGVkTW9kdWxlcyA9IHt9O1xuXG4gXHQvLyBvYmplY3QgdG8gc3RvcmUgbG9hZGVkIENTUyBjaHVua3NcbiBcdHZhciBpbnN0YWxsZWRDc3NDaHVua3MgPSB7XG4gXHRcdFwiaW5kZXhcIjogMFxuIFx0fTtcblxuIFx0Ly8gb2JqZWN0IHRvIHN0b3JlIGxvYWRlZCBhbmQgbG9hZGluZyBjaHVua3NcbiBcdC8vIHVuZGVmaW5lZCA9IGNodW5rIG5vdCBsb2FkZWQsIG51bGwgPSBjaHVuayBwcmVsb2FkZWQvcHJlZmV0Y2hlZFxuIFx0Ly8gUHJvbWlzZSA9IGNodW5rIGxvYWRpbmcsIDAgPSBjaHVuayBsb2FkZWRcbiBcdHZhciBpbnN0YWxsZWRDaHVua3MgPSB7XG4gXHRcdFwiaW5kZXhcIjogMFxuIFx0fTtcblxuIFx0dmFyIGRlZmVycmVkTW9kdWxlcyA9IFtdO1xuXG4gXHQvLyBzY3JpcHQgcGF0aCBmdW5jdGlvblxuIFx0ZnVuY3Rpb24ganNvbnBTY3JpcHRTcmMoY2h1bmtJZCkge1xuIFx0XHRyZXR1cm4gX193ZWJwYWNrX3JlcXVpcmVfXy5wICsgXCJcIiArICh7XCJsb2dpblwiOlwibG9naW5cIixcInRvcC1uYXYtdXNlclwiOlwidG9wLW5hdi11c2VyXCJ9W2NodW5rSWRdfHxjaHVua0lkKSArIFwiLmJ1bmRsZS5qc1wiXG4gXHR9XG5cbiBcdC8vIFRoZSByZXF1aXJlIGZ1bmN0aW9uXG4gXHRmdW5jdGlvbiBfX3dlYnBhY2tfcmVxdWlyZV9fKG1vZHVsZUlkKSB7XG5cbiBcdFx0Ly8gQ2hlY2sgaWYgbW9kdWxlIGlzIGluIGNhY2hlXG4gXHRcdGlmKGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdKSB7XG4gXHRcdFx0cmV0dXJuIGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdLmV4cG9ydHM7XG4gXHRcdH1cbiBcdFx0Ly8gQ3JlYXRlIGEgbmV3IG1vZHVsZSAoYW5kIHB1dCBpdCBpbnRvIHRoZSBjYWNoZSlcbiBcdFx0dmFyIG1vZHVsZSA9IGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdID0ge1xuIFx0XHRcdGk6IG1vZHVsZUlkLFxuIFx0XHRcdGw6IGZhbHNlLFxuIFx0XHRcdGV4cG9ydHM6IHt9XG4gXHRcdH07XG5cbiBcdFx0Ly8gRXhlY3V0ZSB0aGUgbW9kdWxlIGZ1bmN0aW9uXG4gXHRcdG1vZHVsZXNbbW9kdWxlSWRdLmNhbGwobW9kdWxlLmV4cG9ydHMsIG1vZHVsZSwgbW9kdWxlLmV4cG9ydHMsIF9fd2VicGFja19yZXF1aXJlX18pO1xuXG4gXHRcdC8vIEZsYWcgdGhlIG1vZHVsZSBhcyBsb2FkZWRcbiBcdFx0bW9kdWxlLmwgPSB0cnVlO1xuXG4gXHRcdC8vIFJldHVybiB0aGUgZXhwb3J0cyBvZiB0aGUgbW9kdWxlXG4gXHRcdHJldHVybiBtb2R1bGUuZXhwb3J0cztcbiBcdH1cblxuIFx0Ly8gVGhpcyBmaWxlIGNvbnRhaW5zIG9ubHkgdGhlIGVudHJ5IGNodW5rLlxuIFx0Ly8gVGhlIGNodW5rIGxvYWRpbmcgZnVuY3Rpb24gZm9yIGFkZGl0aW9uYWwgY2h1bmtzXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLmUgPSBmdW5jdGlvbiByZXF1aXJlRW5zdXJlKGNodW5rSWQpIHtcbiBcdFx0dmFyIHByb21pc2VzID0gW107XG5cblxuIFx0XHQvLyBtaW5pLWNzcy1leHRyYWN0LXBsdWdpbiBDU1MgbG9hZGluZ1xuIFx0XHR2YXIgY3NzQ2h1bmtzID0ge1wibG9naW5cIjoxfTtcbiBcdFx0aWYoaW5zdGFsbGVkQ3NzQ2h1bmtzW2NodW5rSWRdKSBwcm9taXNlcy5wdXNoKGluc3RhbGxlZENzc0NodW5rc1tjaHVua0lkXSk7XG4gXHRcdGVsc2UgaWYoaW5zdGFsbGVkQ3NzQ2h1bmtzW2NodW5rSWRdICE9PSAwICYmIGNzc0NodW5rc1tjaHVua0lkXSkge1xuIFx0XHRcdHByb21pc2VzLnB1c2goaW5zdGFsbGVkQ3NzQ2h1bmtzW2NodW5rSWRdID0gbmV3IFByb21pc2UoZnVuY3Rpb24ocmVzb2x2ZSwgcmVqZWN0KSB7XG4gXHRcdFx0XHR2YXIgaHJlZiA9IFwiXCIgKyAoe1wibG9naW5cIjpcImxvZ2luXCIsXCJ0b3AtbmF2LXVzZXJcIjpcInRvcC1uYXYtdXNlclwifVtjaHVua0lkXXx8Y2h1bmtJZCkgKyBcIi5jc3NcIjtcbiBcdFx0XHRcdHZhciBmdWxsaHJlZiA9IF9fd2VicGFja19yZXF1aXJlX18ucCArIGhyZWY7XG4gXHRcdFx0XHR2YXIgZXhpc3RpbmdMaW5rVGFncyA9IGRvY3VtZW50LmdldEVsZW1lbnRzQnlUYWdOYW1lKFwibGlua1wiKTtcbiBcdFx0XHRcdGZvcih2YXIgaSA9IDA7IGkgPCBleGlzdGluZ0xpbmtUYWdzLmxlbmd0aDsgaSsrKSB7XG4gXHRcdFx0XHRcdHZhciB0YWcgPSBleGlzdGluZ0xpbmtUYWdzW2ldO1xuIFx0XHRcdFx0XHR2YXIgZGF0YUhyZWYgPSB0YWcuZ2V0QXR0cmlidXRlKFwiZGF0YS1ocmVmXCIpIHx8IHRhZy5nZXRBdHRyaWJ1dGUoXCJocmVmXCIpO1xuIFx0XHRcdFx0XHRpZih0YWcucmVsID09PSBcInN0eWxlc2hlZXRcIiAmJiAoZGF0YUhyZWYgPT09IGhyZWYgfHwgZGF0YUhyZWYgPT09IGZ1bGxocmVmKSkgcmV0dXJuIHJlc29sdmUoKTtcbiBcdFx0XHRcdH1cbiBcdFx0XHRcdHZhciBleGlzdGluZ1N0eWxlVGFncyA9IGRvY3VtZW50LmdldEVsZW1lbnRzQnlUYWdOYW1lKFwic3R5bGVcIik7XG4gXHRcdFx0XHRmb3IodmFyIGkgPSAwOyBpIDwgZXhpc3RpbmdTdHlsZVRhZ3MubGVuZ3RoOyBpKyspIHtcbiBcdFx0XHRcdFx0dmFyIHRhZyA9IGV4aXN0aW5nU3R5bGVUYWdzW2ldO1xuIFx0XHRcdFx0XHR2YXIgZGF0YUhyZWYgPSB0YWcuZ2V0QXR0cmlidXRlKFwiZGF0YS1ocmVmXCIpO1xuIFx0XHRcdFx0XHRpZihkYXRhSHJlZiA9PT0gaHJlZiB8fCBkYXRhSHJlZiA9PT0gZnVsbGhyZWYpIHJldHVybiByZXNvbHZlKCk7XG4gXHRcdFx0XHR9XG4gXHRcdFx0XHR2YXIgbGlua1RhZyA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoXCJsaW5rXCIpO1xuXG4gXHRcdFx0XHRsaW5rVGFnLnJlbCA9IFwic3R5bGVzaGVldFwiO1xuIFx0XHRcdFx0bGlua1RhZy50eXBlID0gXCJ0ZXh0L2Nzc1wiO1xuIFx0XHRcdFx0bGlua1RhZy5vbmxvYWQgPSByZXNvbHZlO1xuIFx0XHRcdFx0bGlua1RhZy5vbmVycm9yID0gZnVuY3Rpb24oZXZlbnQpIHtcbiBcdFx0XHRcdFx0dmFyIHJlcXVlc3QgPSBldmVudCAmJiBldmVudC50YXJnZXQgJiYgZXZlbnQudGFyZ2V0LmhyZWYgfHwgZnVsbGhyZWY7XG4gXHRcdFx0XHRcdHZhciBlcnIgPSBuZXcgRXJyb3IoXCJMb2FkaW5nIENTUyBjaHVuayBcIiArIGNodW5rSWQgKyBcIiBmYWlsZWQuXFxuKFwiICsgcmVxdWVzdCArIFwiKVwiKTtcbiBcdFx0XHRcdFx0ZXJyLmNvZGUgPSBcIkNTU19DSFVOS19MT0FEX0ZBSUxFRFwiO1xuIFx0XHRcdFx0XHRlcnIucmVxdWVzdCA9IHJlcXVlc3Q7XG4gXHRcdFx0XHRcdGRlbGV0ZSBpbnN0YWxsZWRDc3NDaHVua3NbY2h1bmtJZF1cbiBcdFx0XHRcdFx0bGlua1RhZy5wYXJlbnROb2RlLnJlbW92ZUNoaWxkKGxpbmtUYWcpXG4gXHRcdFx0XHRcdHJlamVjdChlcnIpO1xuIFx0XHRcdFx0fTtcbiBcdFx0XHRcdGxpbmtUYWcuaHJlZiA9IGZ1bGxocmVmO1xuXG4gXHRcdFx0XHRkb2N1bWVudC5oZWFkLmFwcGVuZENoaWxkKGxpbmtUYWcpO1xuIFx0XHRcdH0pLnRoZW4oZnVuY3Rpb24oKSB7XG4gXHRcdFx0XHRpbnN0YWxsZWRDc3NDaHVua3NbY2h1bmtJZF0gPSAwO1xuIFx0XHRcdH0pKTtcbiBcdFx0fVxuXG4gXHRcdC8vIEpTT05QIGNodW5rIGxvYWRpbmcgZm9yIGphdmFzY3JpcHRcblxuIFx0XHR2YXIgaW5zdGFsbGVkQ2h1bmtEYXRhID0gaW5zdGFsbGVkQ2h1bmtzW2NodW5rSWRdO1xuIFx0XHRpZihpbnN0YWxsZWRDaHVua0RhdGEgIT09IDApIHsgLy8gMCBtZWFucyBcImFscmVhZHkgaW5zdGFsbGVkXCIuXG5cbiBcdFx0XHQvLyBhIFByb21pc2UgbWVhbnMgXCJjdXJyZW50bHkgbG9hZGluZ1wiLlxuIFx0XHRcdGlmKGluc3RhbGxlZENodW5rRGF0YSkge1xuIFx0XHRcdFx0cHJvbWlzZXMucHVzaChpbnN0YWxsZWRDaHVua0RhdGFbMl0pO1xuIFx0XHRcdH0gZWxzZSB7XG4gXHRcdFx0XHQvLyBzZXR1cCBQcm9taXNlIGluIGNodW5rIGNhY2hlXG4gXHRcdFx0XHR2YXIgcHJvbWlzZSA9IG5ldyBQcm9taXNlKGZ1bmN0aW9uKHJlc29sdmUsIHJlamVjdCkge1xuIFx0XHRcdFx0XHRpbnN0YWxsZWRDaHVua0RhdGEgPSBpbnN0YWxsZWRDaHVua3NbY2h1bmtJZF0gPSBbcmVzb2x2ZSwgcmVqZWN0XTtcbiBcdFx0XHRcdH0pO1xuIFx0XHRcdFx0cHJvbWlzZXMucHVzaChpbnN0YWxsZWRDaHVua0RhdGFbMl0gPSBwcm9taXNlKTtcblxuIFx0XHRcdFx0Ly8gc3RhcnQgY2h1bmsgbG9hZGluZ1xuIFx0XHRcdFx0dmFyIHNjcmlwdCA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoJ3NjcmlwdCcpO1xuIFx0XHRcdFx0dmFyIG9uU2NyaXB0Q29tcGxldGU7XG5cbiBcdFx0XHRcdHNjcmlwdC5jaGFyc2V0ID0gJ3V0Zi04JztcbiBcdFx0XHRcdHNjcmlwdC50aW1lb3V0ID0gMTIwO1xuIFx0XHRcdFx0aWYgKF9fd2VicGFja19yZXF1aXJlX18ubmMpIHtcbiBcdFx0XHRcdFx0c2NyaXB0LnNldEF0dHJpYnV0ZShcIm5vbmNlXCIsIF9fd2VicGFja19yZXF1aXJlX18ubmMpO1xuIFx0XHRcdFx0fVxuIFx0XHRcdFx0c2NyaXB0LnNyYyA9IGpzb25wU2NyaXB0U3JjKGNodW5rSWQpO1xuXG4gXHRcdFx0XHQvLyBjcmVhdGUgZXJyb3IgYmVmb3JlIHN0YWNrIHVud291bmQgdG8gZ2V0IHVzZWZ1bCBzdGFja3RyYWNlIGxhdGVyXG4gXHRcdFx0XHR2YXIgZXJyb3IgPSBuZXcgRXJyb3IoKTtcbiBcdFx0XHRcdG9uU2NyaXB0Q29tcGxldGUgPSBmdW5jdGlvbiAoZXZlbnQpIHtcbiBcdFx0XHRcdFx0Ly8gYXZvaWQgbWVtIGxlYWtzIGluIElFLlxuIFx0XHRcdFx0XHRzY3JpcHQub25lcnJvciA9IHNjcmlwdC5vbmxvYWQgPSBudWxsO1xuIFx0XHRcdFx0XHRjbGVhclRpbWVvdXQodGltZW91dCk7XG4gXHRcdFx0XHRcdHZhciBjaHVuayA9IGluc3RhbGxlZENodW5rc1tjaHVua0lkXTtcbiBcdFx0XHRcdFx0aWYoY2h1bmsgIT09IDApIHtcbiBcdFx0XHRcdFx0XHRpZihjaHVuaykge1xuIFx0XHRcdFx0XHRcdFx0dmFyIGVycm9yVHlwZSA9IGV2ZW50ICYmIChldmVudC50eXBlID09PSAnbG9hZCcgPyAnbWlzc2luZycgOiBldmVudC50eXBlKTtcbiBcdFx0XHRcdFx0XHRcdHZhciByZWFsU3JjID0gZXZlbnQgJiYgZXZlbnQudGFyZ2V0ICYmIGV2ZW50LnRhcmdldC5zcmM7XG4gXHRcdFx0XHRcdFx0XHRlcnJvci5tZXNzYWdlID0gJ0xvYWRpbmcgY2h1bmsgJyArIGNodW5rSWQgKyAnIGZhaWxlZC5cXG4oJyArIGVycm9yVHlwZSArICc6ICcgKyByZWFsU3JjICsgJyknO1xuIFx0XHRcdFx0XHRcdFx0ZXJyb3IubmFtZSA9ICdDaHVua0xvYWRFcnJvcic7XG4gXHRcdFx0XHRcdFx0XHRlcnJvci50eXBlID0gZXJyb3JUeXBlO1xuIFx0XHRcdFx0XHRcdFx0ZXJyb3IucmVxdWVzdCA9IHJlYWxTcmM7XG4gXHRcdFx0XHRcdFx0XHRjaHVua1sxXShlcnJvcik7XG4gXHRcdFx0XHRcdFx0fVxuIFx0XHRcdFx0XHRcdGluc3RhbGxlZENodW5rc1tjaHVua0lkXSA9IHVuZGVmaW5lZDtcbiBcdFx0XHRcdFx0fVxuIFx0XHRcdFx0fTtcbiBcdFx0XHRcdHZhciB0aW1lb3V0ID0gc2V0VGltZW91dChmdW5jdGlvbigpe1xuIFx0XHRcdFx0XHRvblNjcmlwdENvbXBsZXRlKHsgdHlwZTogJ3RpbWVvdXQnLCB0YXJnZXQ6IHNjcmlwdCB9KTtcbiBcdFx0XHRcdH0sIDEyMDAwMCk7XG4gXHRcdFx0XHRzY3JpcHQub25lcnJvciA9IHNjcmlwdC5vbmxvYWQgPSBvblNjcmlwdENvbXBsZXRlO1xuIFx0XHRcdFx0ZG9jdW1lbnQuaGVhZC5hcHBlbmRDaGlsZChzY3JpcHQpO1xuIFx0XHRcdH1cbiBcdFx0fVxuIFx0XHRyZXR1cm4gUHJvbWlzZS5hbGwocHJvbWlzZXMpO1xuIFx0fTtcblxuIFx0Ly8gZXhwb3NlIHRoZSBtb2R1bGVzIG9iamVjdCAoX193ZWJwYWNrX21vZHVsZXNfXylcbiBcdF9fd2VicGFja19yZXF1aXJlX18ubSA9IG1vZHVsZXM7XG5cbiBcdC8vIGV4cG9zZSB0aGUgbW9kdWxlIGNhY2hlXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLmMgPSBpbnN0YWxsZWRNb2R1bGVzO1xuXG4gXHQvLyBkZWZpbmUgZ2V0dGVyIGZ1bmN0aW9uIGZvciBoYXJtb255IGV4cG9ydHNcbiBcdF9fd2VicGFja19yZXF1aXJlX18uZCA9IGZ1bmN0aW9uKGV4cG9ydHMsIG5hbWUsIGdldHRlcikge1xuIFx0XHRpZighX193ZWJwYWNrX3JlcXVpcmVfXy5vKGV4cG9ydHMsIG5hbWUpKSB7XG4gXHRcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsIG5hbWUsIHsgZW51bWVyYWJsZTogdHJ1ZSwgZ2V0OiBnZXR0ZXIgfSk7XG4gXHRcdH1cbiBcdH07XG5cbiBcdC8vIGRlZmluZSBfX2VzTW9kdWxlIG9uIGV4cG9ydHNcbiBcdF9fd2VicGFja19yZXF1aXJlX18uciA9IGZ1bmN0aW9uKGV4cG9ydHMpIHtcbiBcdFx0aWYodHlwZW9mIFN5bWJvbCAhPT0gJ3VuZGVmaW5lZCcgJiYgU3ltYm9sLnRvU3RyaW5nVGFnKSB7XG4gXHRcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsIFN5bWJvbC50b1N0cmluZ1RhZywgeyB2YWx1ZTogJ01vZHVsZScgfSk7XG4gXHRcdH1cbiBcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsICdfX2VzTW9kdWxlJywgeyB2YWx1ZTogdHJ1ZSB9KTtcbiBcdH07XG5cbiBcdC8vIGNyZWF0ZSBhIGZha2UgbmFtZXNwYWNlIG9iamVjdFxuIFx0Ly8gbW9kZSAmIDE6IHZhbHVlIGlzIGEgbW9kdWxlIGlkLCByZXF1aXJlIGl0XG4gXHQvLyBtb2RlICYgMjogbWVyZ2UgYWxsIHByb3BlcnRpZXMgb2YgdmFsdWUgaW50byB0aGUgbnNcbiBcdC8vIG1vZGUgJiA0OiByZXR1cm4gdmFsdWUgd2hlbiBhbHJlYWR5IG5zIG9iamVjdFxuIFx0Ly8gbW9kZSAmIDh8MTogYmVoYXZlIGxpa2UgcmVxdWlyZVxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy50ID0gZnVuY3Rpb24odmFsdWUsIG1vZGUpIHtcbiBcdFx0aWYobW9kZSAmIDEpIHZhbHVlID0gX193ZWJwYWNrX3JlcXVpcmVfXyh2YWx1ZSk7XG4gXHRcdGlmKG1vZGUgJiA4KSByZXR1cm4gdmFsdWU7XG4gXHRcdGlmKChtb2RlICYgNCkgJiYgdHlwZW9mIHZhbHVlID09PSAnb2JqZWN0JyAmJiB2YWx1ZSAmJiB2YWx1ZS5fX2VzTW9kdWxlKSByZXR1cm4gdmFsdWU7XG4gXHRcdHZhciBucyA9IE9iamVjdC5jcmVhdGUobnVsbCk7XG4gXHRcdF9fd2VicGFja19yZXF1aXJlX18ucihucyk7XG4gXHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShucywgJ2RlZmF1bHQnLCB7IGVudW1lcmFibGU6IHRydWUsIHZhbHVlOiB2YWx1ZSB9KTtcbiBcdFx0aWYobW9kZSAmIDIgJiYgdHlwZW9mIHZhbHVlICE9ICdzdHJpbmcnKSBmb3IodmFyIGtleSBpbiB2YWx1ZSkgX193ZWJwYWNrX3JlcXVpcmVfXy5kKG5zLCBrZXksIGZ1bmN0aW9uKGtleSkgeyByZXR1cm4gdmFsdWVba2V5XTsgfS5iaW5kKG51bGwsIGtleSkpO1xuIFx0XHRyZXR1cm4gbnM7XG4gXHR9O1xuXG4gXHQvLyBnZXREZWZhdWx0RXhwb3J0IGZ1bmN0aW9uIGZvciBjb21wYXRpYmlsaXR5IHdpdGggbm9uLWhhcm1vbnkgbW9kdWxlc1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5uID0gZnVuY3Rpb24obW9kdWxlKSB7XG4gXHRcdHZhciBnZXR0ZXIgPSBtb2R1bGUgJiYgbW9kdWxlLl9fZXNNb2R1bGUgP1xuIFx0XHRcdGZ1bmN0aW9uIGdldERlZmF1bHQoKSB7IHJldHVybiBtb2R1bGVbJ2RlZmF1bHQnXTsgfSA6XG4gXHRcdFx0ZnVuY3Rpb24gZ2V0TW9kdWxlRXhwb3J0cygpIHsgcmV0dXJuIG1vZHVsZTsgfTtcbiBcdFx0X193ZWJwYWNrX3JlcXVpcmVfXy5kKGdldHRlciwgJ2EnLCBnZXR0ZXIpO1xuIFx0XHRyZXR1cm4gZ2V0dGVyO1xuIFx0fTtcblxuIFx0Ly8gT2JqZWN0LnByb3RvdHlwZS5oYXNPd25Qcm9wZXJ0eS5jYWxsXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLm8gPSBmdW5jdGlvbihvYmplY3QsIHByb3BlcnR5KSB7IHJldHVybiBPYmplY3QucHJvdG90eXBlLmhhc093blByb3BlcnR5LmNhbGwob2JqZWN0LCBwcm9wZXJ0eSk7IH07XG5cbiBcdC8vIF9fd2VicGFja19wdWJsaWNfcGF0aF9fXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLnAgPSBcIi9kaXN0L1wiO1xuXG4gXHQvLyBvbiBlcnJvciBmdW5jdGlvbiBmb3IgYXN5bmMgbG9hZGluZ1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5vZSA9IGZ1bmN0aW9uKGVycikgeyBjb25zb2xlLmVycm9yKGVycik7IHRocm93IGVycjsgfTtcblxuIFx0dmFyIGpzb25wQXJyYXkgPSB3aW5kb3dbXCJ3ZWJwYWNrSnNvbnBcIl0gPSB3aW5kb3dbXCJ3ZWJwYWNrSnNvbnBcIl0gfHwgW107XG4gXHR2YXIgb2xkSnNvbnBGdW5jdGlvbiA9IGpzb25wQXJyYXkucHVzaC5iaW5kKGpzb25wQXJyYXkpO1xuIFx0anNvbnBBcnJheS5wdXNoID0gd2VicGFja0pzb25wQ2FsbGJhY2s7XG4gXHRqc29ucEFycmF5ID0ganNvbnBBcnJheS5zbGljZSgpO1xuIFx0Zm9yKHZhciBpID0gMDsgaSA8IGpzb25wQXJyYXkubGVuZ3RoOyBpKyspIHdlYnBhY2tKc29ucENhbGxiYWNrKGpzb25wQXJyYXlbaV0pO1xuIFx0dmFyIHBhcmVudEpzb25wRnVuY3Rpb24gPSBvbGRKc29ucEZ1bmN0aW9uO1xuXG5cbiBcdC8vIGFkZCBlbnRyeSBtb2R1bGUgdG8gZGVmZXJyZWQgbGlzdFxuIFx0ZGVmZXJyZWRNb2R1bGVzLnB1c2goW1wiLi9icm93c2VyL3NyYy9pbmRleC5qc1wiLFwidmVuZG9yXCJdKTtcbiBcdC8vIHJ1biBkZWZlcnJlZCBtb2R1bGVzIHdoZW4gcmVhZHlcbiBcdHJldHVybiBjaGVja0RlZmVycmVkTW9kdWxlcygpO1xuIiwiZXhwb3J0IGRlZmF1bHQgXCJkYXRhOmltYWdlL3BuZztiYXNlNjQsaVZCT1J3MEtHZ29BQUFBTlNVaEVVZ0FBQU1BQUFBQTBDQU1BQUFEckFjYzZBQUFBQkdkQlRVRUFBSy9JTndXSzZRQUFBQmwwUlZoMFUyOW1kSGRoY21VQVFXUnZZbVVnU1cxaFoyVlNaV0ZrZVhISlpUd0FBQUFQVUV4VVJmK2pSLy8vLy9nNEFJc1hBQUFBQUxFQm1BMEFBQUhhU1VSQlZIamEzRmRia3NNZ0RET0crNTk1ZVdhVC9NUUM0VTdDZEp0VjJrb1JOaGhMUW9mMlA4dVFPdmJ4RjRtRWZGYzd0MUVnU0pEOENydjRRUU5hK1JWZ3o1UGZYcHY0Snd4b0dlWmY0UVl3ZmpTRkdqMHdSeHJLRWdpNml4OHpJR1YrcFBJYkpUVDBvWHY0UVFObGJ1UjRNLzBnMU9XYkRlZ08vamtEWW81eWVmNGFBN1U1UVBsbkRJZ01pV2VCL0xXZ3pVRDVSOWo4c0lITTJjcFNueVNEQVIwRzFHQUE1Y2NOMUFCTDNWZXNCbG9hMWMxUjJQeFRCa3BWclJMR25FNGpvVlg1L0hOcm9PNktXd3pBL1BNR3JQemorWTFMRXViSGp4SlZvcVVvVkZsMUZ6OThsRWpRQTZXNkx5b3lvUmovTUJEajlkWXFmaHBjUGNsNDNJbjk4elg4L1B4VVBZbHgzR21YVld4NWZxWmVpOER4Q2VGcWNzQzdmc1RBT2JhcjJMb0dTSHEzQ01TMGpLRUlyT3U5UDRWaWQzSXh1SUJ0dXhCUDcycmdMakNCSVFNRUxLelV4d3p3OUw1V0IxNjRDNTExNzR0eEJ0dVBFaHk5bzVDMWxmRmZhS2F4c1pDeDlDVEZka0R0NFVuTCtORUFWMDlHUEU3MWVRa2JBa0RWay9zZXNvcUJES0xvYmVqSWZERy9JM1BHOUk3TUcvTXJzZlAxSXdhWUhaa3o1cCtGblBIN1U0amRJWGxqZWtmbWplV2xxYit4SS90dEhYamhMc1R1a0x3eHZ5Tnp4dnlPekJuVE96SnZUTy9JdlBHZkFBTUFhQkdJaFJTdzdIa0FBQUFBU1VWT1JLNUNZSUk9XCIiLCJleHBvcnQgZGVmYXVsdCBcImRhdGE6aW1hZ2UvZ2lmO2Jhc2U2NCxSMGxHT0RsaEVRQVFBTVFRQUNrb0tWcFpNZWZEUXQySVZQZlhXclhQakJoSjNpRmhRbXN3R0p4cEVHTytZLy9uclVLV1V0NnVBUGVPUXNaSkdQLy8vd0FBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQ0gvQzA1RlZGTkRRVkJGTWk0d0F3RUFBQUFoK1FRSkZBQVFBQ3dBQUFBQUVRQVFBQUFGalNBa2p0RGpQR1JLUG94ekptTFRpQWtNTVl6U1BzMnlOQVFJc0pHUU5YSzRoY0h3Z3hRSlBWOUR4MUF5WVlTaTljZDRlS1V3WUhXNVNKaGN2TVRCR2NpQlRZZ1Q0N0NHSEJSNDNZTUFpRDkwZFRrS0JRVjdBSDBuZUhWM0JUNG1pQThGZ0NJSERGRm5KNFZlS0NVT2xvNWVoU2NublNZNFJqS1lLSnNuRDVVNENIRW5DQ05lc1NvSUQ3UVFJUUFoK1FRRkZBQVFBQ3dBQUFBQUVRQVFBQUFGakNBa2p0RGpQR1JLUG94ekptTFRpQWtNTVl6U1BzMnlOQVFJc0pHUU5YSzRoY0h3Z3hRSlBWOUR4MUF5WVlTaTljZDRlS1V3WUhXNVNKaGN2TVRCR2NpQlRZZ1Q0N0NHSEJSNDNZTUFpRDkwZFRrS0JRVW1mU2QvQ25WM0JUNW5KNFNBSWw0UEE0NWVrUUp6a3ljblBqNEZDcG9ubkdoR01qaWRLQWhuQ0FnNGMxNklLQkNzQ0NxdVhpSWhBRHM9XCIiLCJleHBvcnQgZGVmYXVsdCBcImRhdGE6aW1hZ2UvcG5nO2Jhc2U2NCxpVkJPUncwS0dnb0FBQUFOU1VoRVVnQUFBQm9BQUFBY0NBWUFBQUIvRTYvVEFBQUFDWEJJV1hNQUFBc1RBQUFMRXdFQW1wd1lBQUFLVDJsRFExQlFhRzkwYjNOb2IzQWdTVU5ESUhCeWIyWnBiR1VBQUhqYW5WTm5WRlBwRmozMzN2UkNTNGlBbEV0dlVoVUlJRkpDaTRBVWtTWXFJUWtRU29naG9ka1ZVY0VSUlVVRUc4aWdpQU9Pam9DTUZWRXNESW9LMkFma0lhS09nNk9JaXNyNzRYdWphOWE4OStiTi9yWFhQdWVzODUyenp3ZkFDQXlXU0ROUk5ZQU1xVUllRWVDRHg4VEc0ZVF1UUlFS0pIQUFFQWl6WkNGei9TTUJBUGgrUER3cklzQUh2Z0FCZU5NTENBREFUWnZBTUJ5SC93L3FRcGxjQVlDRUFjQjBrVGhMQ0lBVUFFQjZqa0ttQUVCR0FZQ2RtQ1pUQUtBRUFHRExZMkxqQUZBdEFHQW5mK2JUQUlDZCtKbDdBUUJibENFVkFhQ1JBQ0FUWlloRUFHZzdBS3pQVm9wRkFGZ3dBQlJtUzhRNUFOZ3RBREJKVjJaSUFMQzNBTURPRUF1eUFBZ01BREJSaUlVcEFBUjdBR0RJSXlONEFJU1pBQlJHOGxjODhTdXVFT2NxQUFCNG1iSTh1U1E1UllGYkNDMXhCMWRYTGg0b3pra1hLeFEyWVFKaG1rQXV3bm1aR1RLQk5BL2c4OHdBQUtDUkZSSGdnL1A5ZU00T3JzN09ObzYyRGw4dDZyOEcveUppWXVQKzVjK3JjRUFBQU9GMGZ0SCtMQyt6R29BN0JvQnQvcUlsN2dSb1hndWdkZmVMWnJJUFFMVUFvT25hVi9OdytINDhQRVdoa0xuWjJlWGs1TmhLeEVKYlljcFhmZjVud2wvQVYvMXMrWDQ4L1BmMTRMN2lKSUV5WFlGSEJQamd3c3owVEtVY3o1SUpoR0xjNW85SC9MY0wvL3dkMHlMRVNXSzVXQ29VNDFFU2NZNUVtb3p6TXFVaWlVS1NLY1VsMHY5azR0OHMrd00rM3pVQXNHbytBWHVSTGFoZFl3UDJTeWNRV0hUQTR2Y0FBUEs3YjhIVUtBZ0RnR2lENGM5My8rOC8vVWVnSlFDQVprbVNjUUFBWGtRa0xsVEtzei9IQ0FBQVJLQ0JLckJCRy9UQkdDekFCaHpCQmR6QkMveGdOb1JDSk1UQ1FoQkNDbVNBSEhKZ0theUNRaWlHemJBZEttQXYxRUFkTk1CUmFJYVRjQTR1d2xXNERqMXdEL3BoQ0o3QktMeUJDUVJCeUFnVFlTSGFpQUZpaWxnampnZ1htWVg0SWNGSUJCS0xKQ0RKaUJSUklrdVJOVWd4VW9wVUlGVklIZkk5Y2dJNWgxeEd1cEU3eUFBeWd2eUd2RWN4bElHeVVUM1VETFZEdWFnM0dvUkdvZ3ZRWkhReG1vOFdvSnZRY3JRYVBZdzJvZWZRcTJnUDJvOCtROGN3d09nWUJ6UEViREF1eHNOQ3NUZ3NDWk5qeTdFaXJBeXJ4aHF3VnF3RHU0bjFZOCt4ZHdRU2dVWEFDVFlFZDBJZ1lSNUJTRmhNV0U3WVNLZ2dIQ1EwRWRvSk53a0RoRkhDSnlLVHFFdTBKcm9SK2NRWVlqSXhoMWhJTENQV0VvOFRMeEI3aUVQRU55UVNpVU15SjdtUUFrbXhwRlRTRXRKRzBtNVNJK2tzcVpzMFNCb2prOG5hWkd1eUJ6bVVMQ0FyeUlYa25lVEQ1RFBrRytRaDhsc0tuV0pBY2FUNFUrSW9Vc3BxU2hubEVPVTA1UVpsbURKQlZhT2FVdDJvb1ZRUk5ZOWFRcTJodGxLdlVZZW9FelIxbWpuTmd4WkpTNld0b3BYVEdtZ1hhUGRwcitoMHVoSGRsUjVPbDlCWDBzdnBSK2lYNkFQMGR3d05oaFdEeDRobktCbWJHQWNZWnhsM0dLK1lUS1laMDRzWngxUXdOekhybU9lWkQ1bHZWVmdxdGlwOEZaSEtDcFZLbFNhVkd5b3ZWS21xcHFyZXFndFY4MVhMVkkrcFhsTjlya1pWTTFQanFRblVscXRWcXAxUTYxTWJVMmVwTzZpSHFtZW9iMVEvcEg1Wi9Za0dXY05NdzA5RHBGR2dzVi9qdk1ZZ0MyTVpzM2dzSVdzTnE0WjFnVFhFSnJITjJYeDJLcnVZL1IyN2l6MnFxYUU1UXpOS00xZXpVdk9VWmo4SDQ1aHgrSngwVGdubktLZVg4MzZLM2hUdktlSXBHNlkwVExreFpWeHJxcGFYbGxpclNLdFJxMGZydlRhdTdhZWRwcjFGdTFuN2dRNUJ4MG9uWENkSFo0L09CWjNuVTlsVDNhY0tweFpOUFRyMXJpNnFhNlVib2J0RWQ3OXVwKzZZbnI1ZWdKNU1iNmZlZWIzbitoeDlMLzFVL1czNnAvVkhERmdHc3d3a0J0c016aGc4eFRWeGJ6d2RMOGZiOFZGRFhjTkFRNlZobFdHWDRZU1J1ZEU4bzlWR2pVWVBqR25HWE9NazQyM0diY2FqSmdZbUlTWkxUZXBON3BwU1RibW1LYVk3VER0TXg4M016YUxOMXBrMW16MHgxekxubStlYjE1dmZ0MkJhZUZvc3RxaTJ1R1ZKc3VSYXBsbnV0cnh1aFZvNVdhVllWVnBkczBhdG5hMGwxcnV0dTZjUnA3bE9rMDZybnRabnc3RHh0c20ycWJjWnNPWFlCdHV1dG0yMmZXRm5ZaGRudDhXdXcrNlR2Wk45dW4yTi9UMEhEWWZaRHFzZFdoMStjN1J5RkRwV090NmF6cHp1UDMzRjlKYnBMMmRZenhEUDJEUGp0aFBMS2NScG5WT2IwMGRuRjJlNWM0UHppSXVKUzRMTExwYytMcHNieHQzSXZlUktkUFZ4WGVGNjB2V2RtN09id3UybzI2L3VOdTVwN29mY244dzBueW1lV1ROejBNUElRK0JSNWRFL0M1K1ZNR3Zmckg1UFEwK0JaN1huSXk5akw1RlhyZGV3dDZWM3F2ZGg3eGMrOWo1eW4rTSs0enczM2pMZVdWL01OOEMzeUxmTFQ4TnZubCtGMzBOL0kvOWsvM3IvMFFDbmdDVUJad09KZ1VHQld3TDcrSHA4SWIrT1B6cmJaZmF5MmUxQmpLQzVRUlZCajRLdGd1WEJyU0ZveU95UXJTSDM1NWpPa2M1cERvVlFmdWpXMEFkaDVtR0x3MzRNSjRXSGhWZUdQNDV3aUZnYTBUR1hOWGZSM0VOejMwVDZSSlpFM3B0bk1VODVyeTFLTlNvK3FpNXFQTm8zdWpTNlA4WXVabG5NMVZpZFdFbHNTeHc1TGlxdU5tNXN2dC84N2ZPSDRwM2lDK043RjVndnlGMXdlYUhPd3ZTRnB4YXBMaElzT3BaQVRJaE9PSlR3UVJBcXFCYU1KZklUZHlXT0NubkNIY0puSWkvUk50R0kyRU5jS2g1TzhrZ3FUWHFTN0pHOE5Ya2t4VE9sTE9XNWhDZXBrTHhNRFV6ZG16cWVGcHAySUcweVBUcTlNWU9Ta1pCeFFxb2hUWk8yWitwbjVtWjJ5NnhsaGJMK3hXNkx0eThlbFFmSmE3T1FyQVZaTFFxMlFxYm9WRm9vMXlvSHNtZGxWMmEvelluS09aYXJuaXZON2N5enl0dVFONXp2bi8vdEVzSVM0WksycFlaTFZ5MGRXT2E5ckdvNXNqeHhlZHNLNHhVRks0WldCcXc4dUlxMkttM1ZUNnZ0VjVldWZyMG1lazFyZ1Y3QnlvTEJ0UUZyNnd0VkN1V0ZmZXZjMSsxZFQxZ3ZXZCsxWWZxR25ScytGWW1LcmhUYkY1Y1ZmOWdvM0hqbEc0ZHZ5citaM0pTMHFhdkV1V1RQWnRKbTZlYmVMWjViRHBhcWwrYVhEbTROMmRxMERkOVd0TzMxOWtYYkw1Zk5LTnU3ZzdaRHVhTy9QTGk4WmFmSnpzMDdQMVNrVlBSVStsUTI3dExkdFdIWCtHN1I3aHQ3dlBZMDdOWGJXN3ozL1Q3SnZ0dFZBVlZOMVdiVlpmdEorN1AzUDY2SnF1bjRsdnR0WGExT2JYSHR4d1BTQS8wSEl3NjIxN25VMVIzU1BWUlNqOVlyNjBjT3h4KysvcDN2ZHkwTk5nMVZqWnpHNGlOd1JIbms2ZmNKMy9jZURUcmFkb3g3ck9FSDB4OTJIV2NkTDJwQ212S2FScHRUbXZ0YllsdTZUOHcrMGRicTNucjhSOXNmRDV3MFBGbDVTdk5VeVduYTZZTFRrMmZ5ejR5ZGxaMTlmaTc1M0dEYm9yWjc1MlBPMzJvUGIrKzZFSFRoMGtYL2krYzd2RHZPWFBLNGRQS3kyK1VUVjdoWG1xODZYMjNxZE9vOC9wUFRUOGU3bkx1YXJybGNhN251ZXIyMWUyYjM2UnVlTjg3ZDlMMTU4UmIvMXRXZU9UM2R2Zk42Yi9mRjkvWGZGdDErY2lmOXpzdTcyWGNuN3EyOFQ3eGY5RUR0UWRsRDNZZlZQMXYrM05qdjNIOXF3SGVnODlIY1IvY0doWVBQL3BIMWp3OURCWStaajh1R0RZYnJuamcrT1RuaVAzTDk2ZnluUTg5a3p5YWVGLzZpL3N1dUZ4WXZmdmpWNjlmTzBaalJvWmZ5bDVPL2JYeWwvZXJBNnhtdjI4YkN4aDYreVhnek1WNzBWdnZ0d1hmY2R4M3ZvOThQVCtSOElIOG8vMmo1c2ZWVDBLZjdreG1Uay84RUE1anovR016TGRzQUFBQUVaMEZOUVFBQXNZNTgrMUdUQUFBQUlHTklVazBBQUhvbEFBQ0Fnd0FBK2Y4QUFJRHBBQUIxTUFBQTZtQUFBRHFZQUFBWGI1SmZ4VVlBQUFDcVNVUkJWSGphMUpUUkRjTWdERVRQVVVZaHcyU1lNZ3pMWkJoN0YrY2pvaDlPYUVGdHdMa3ZCRUwyOHgyUXFnSUFKTkt4K0xOQ1VnS0FDWjFFL0lJQ1FGaHZLaUFKWFlubTBvRnNqVjU4bWNnNG9sWVNlNjlFTnQ0anEyVkpsL3ZNOFRKbEdxSVRvanpqM0ptVjdWUzRydEI0ajA2ZGI1OEpTaDc2VFYxdEd1MGsvQksxcHRFL1VVNGRJemI5MnQySmZpNUVrb3ErUFR0MXp5T3FmUy9qaWQ1L2xUaVBkNjMyQVFCNG9UTUI0MlBWN0FBQUFBQkpSVTVFcmtKZ2dnPT1cIiIsImltcG9ydCBSZWFjdCBmcm9tICdyZWFjdCc7XG5cbmltcG9ydCBzdG9yYWdlQXZhaWxhYmxlIGZyb20gJy4uLy4uL2xpYi9zdG9yYWdlLWF2YWlsYWJsZS5qcyc7XG5pbXBvcnQgd2VsY29tZSBmcm9tICcuLi8uLi8uLi9pbWFnZXMvY29sb3Bob25fd2VsY29tZS5wbmcnO1xuXG5leHBvcnQgZGVmYXVsdCBmdW5jdGlvbiBDb2xvcGhvbigpIHtcbiAgICBpZiAoIXN0b3JhZ2VBdmFpbGFibGUoJ2xvY2FsU3RvcmFnZScpKSB7XG4gICAgICAgIHJldHVybiAnJztcbiAgICB9XG4gICAgLy8gUHJldmVudCBzYXZpbmcgaGlkZGVuIHN0YXR1czpcbiAgICAvLyBsb2NhbFN0b3JhZ2UuY2xlYXIoKTtcblxuICAgIGNvbnN0IFtvcGVuLCBzZXRPcGVuXSA9IFJlYWN0LnVzZVN0YXRlKHRydWUpO1xuXG4gICAgZnVuY3Rpb24gaGFuZGxlQ2xvc2UoZXZlbnQpIHtcbiAgICAgICAgZXZlbnQucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgc2V0T3BlbihmYWxzZSk7XG4gICAgICAgIGxvY2FsU3RvcmFnZS5zZXRJdGVtKCdjb2xvcGhvbicsICdjbG9zZWQnKTtcbiAgICB9XG5cbiAgICBpZiAob3BlbiAmJiBsb2NhbFN0b3JhZ2UuZ2V0SXRlbSgnY29sb3Bob24nKSAhPT0gJ2Nsb3NlZCcpIHtcbiAgICAgICAgcmV0dXJuIChcbiAgICAgICAgICAgIDxkaXYgY2xhc3NOYW1lPVwiY29udGFpbmVyIGRhcmsgdHlwZS1yZXRyb1wiIHN0eWxlPXt7IHBvc2l0aW9uOiAnZml4ZWQnLCB6SW5kZXg6IDk5OSwgcmlnaHQ6IDAsIGJvdHRvbTogMCwgbGVmdDogMCwgY29sb3I6ICcjQkJCJyB9fT5cbiAgICAgICAgICAgICAgICA8ZGl2IHN0eWxlPXt7IHBhZGRpbmc6ICc1MHB4IDAgNDBweCAwJywgbWFyZ2luOiAnMCBhdXRvJywgdGV4dEFsaWduOiAnY2VudGVyJyB9fT5cbiAgICAgICAgICAgICAgICAgICAgU3RhcnQgR2FtZSBpcyB0aGUgc2VjcmV0XG4gICAgICAgICAgICAgICAgICAgIDxiciAvPlxuICAgICAgICAgICAgICAgICAgICA8YSBocmVmPVwiI2Nsb3NlXCIgdGl0bGU9XCJoaWRlIHRoaXMgbWVzc2FnZSBhbmQgZG9uJ3Qgc2hvdyBpdCB0byBtZSBhZ2FpblwiIGNsYXNzTmFtZT1cInRvb2x0aXBcIiBvbkNsaWNrPXtoYW5kbGVDbG9zZX0+Tm93IFBheSBtZSBmb3IgdGhlIGRvb3IgcmVwYWlyIGNoYXJnZTwvYT5cbiAgICAgICAgICAgICAgICA8L2Rpdj5cbiAgICAgICAgICAgICAgICA8ZGl2IHN0eWxlPXt7IHBvc2l0aW9uOiAnYWJzb2x1dGUnLCB6SW5kZXg6IDIsIHRvcDonMjBweCcsIGxlZnQ6ICc1MCUnLCB3aWR0aDonMTkycHgnLCBoZWlnaHQ6JzE2cHgnLCBtYXJnaW46ICcwIDAgMCAtOTZweCcsIGJhY2tncm91bmQ6IGB1cmwoJHt3ZWxjb21lfSkgbm8tcmVwZWF0IDAgMGAgfX0gLz5cbiAgICAgICAgICAgICAgICA8ZGl2IHN0eWxlPXt7IHBvc2l0aW9uOiAnYWJzb2x1dGUnLCB6SW5kZXg6IDIsIGJvdHRvbTonMCcsIGxlZnQ6ICc1MCUnLCB3aWR0aDonMTkycHgnLCBoZWlnaHQ6JzE4cHgnLCBtYXJnaW46ICcwIDAgMCAtOTZweCcsIGJhY2tncm91bmQ6IGB1cmwoJHt3ZWxjb21lfSkgbm8tcmVwZWF0IDAgLTE2cHhgIH19IC8+XG4gICAgICAgICAgICAgICAgPGRpdiBzdHlsZT17eyBwb3NpdGlvbjogJ2Fic29sdXRlJywgekluZGV4OiAxLCByaWdodDonMCcsIGJvdHRvbTogJzAnLCBsZWZ0OicwJywgd2lkdGg6JzEwMCUnLCBoZWlnaHQ6ICcxOHB4JywgYmFja2dyb3VuZDogYHVybCgke3dlbGNvbWV9KSByZXBlYXQteCAwIC0zNHB4YCB9fSAvPlxuICAgICAgICAgICAgPC9kaXY+XG4gICAgICAgICk7XG4gICAgfVxuXG4gICAgcmV0dXJuICcnO1xufVxuIiwiaW1wb3J0IFJlYWN0IGZyb20gJ3JlYWN0JztcbmltcG9ydCBUb3BOYXYgZnJvbSAnLi9Ub3BOYXYuanN4JztcbmltcG9ydCBTZWFyY2ggZnJvbSAnLi9TZWFyY2guanN4JztcbmltcG9ydCBCdXR0b24gZnJvbSAnLi4vdWkvQnV0dG9uLmpzeCc7XG5pbXBvcnQgeyBRdWVzdGlvbkJsb2NrLCBMb2FkaW5nTWFzY290IH0gZnJvbSAnLi4vLi4vbGliL2ljb25zLmpzJztcblxuY29uc3QgVG9wTmF2VXNlciA9IFJlYWN0LmxhenkoKCkgPT4gaW1wb3J0KC8qIHdlYnBhY2tDaHVua05hbWU6IFwidG9wLW5hdi11c2VyXCIgKi8nLi9Ub3BOYXZVc2VyLmpzeCcpKTtcbmNvbnN0IExvZ2luID0gUmVhY3QubGF6eSgoKSA9PiBpbXBvcnQoLyogd2VicGFja0NodW5rTmFtZTogXCJsb2dpblwiICovJy4vTG9naW4uanN4JykpO1xuXG5mdW5jdGlvbiBIZWFkZXJVc2VyKHsgdXNlcm5hbWUgfSkge1xuICAgIGNvbnN0IFtsb2dpbkxvYWRlZCwgc2V0TG9naW5Mb2FkZWRdID0gUmVhY3QudXNlU3RhdGUoZmFsc2UpO1xuICAgIGNvbnN0IGxhenlsb2FkTG9naW4gPSAoZXZlbnQpID0+IHtcbiAgICAgICAgZXZlbnQucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgc2V0TG9naW5Mb2FkZWQodHJ1ZSk7XG4gICAgfTtcblxuICAgIGNvbnN0IExvZ2luQnV0dG9uID0gKHsgaGFuZGxlQ2xpY2sgfSkgPT4gKFxuICAgICAgICA8QnV0dG9uIHRpdGxlPVwiTG9naW5cIiBvbkNsaWNrPXtoYW5kbGVDbGlja30gY2xhc3Nlcz17eyAnYnV0dG9uLWhlYWRlcic6IHRydWUgfX0+XG4gICAgICAgICAgICA8UXVlc3Rpb25CbG9jayAvPlxuICAgICAgICA8L0J1dHRvbj5cbiAgICApO1xuXG4gICAgcmV0dXJuIChcbiAgICAgICAgPGRpdiBpZD1cImxvZ2luXCI+XG4gICAgICAgICAgICB7dXNlcm5hbWUgPyAoXG4gICAgICAgICAgICAgICAgPFJlYWN0LlN1c3BlbnNlIGZhbGxiYWNrPVwiVXNlclwiPjxUb3BOYXZVc2VyIHVzZXJuYW1lPXt1c2VybmFtZX0gLz48L1JlYWN0LlN1c3BlbnNlPlxuICAgICAgICAgICAgKSA6IChcbiAgICAgICAgICAgICAgICA8PlxuICAgICAgICAgICAgICAgICAgICB7bG9naW5Mb2FkZWRcbiAgICAgICAgICAgICAgICAgICAgICAgID8gPFJlYWN0LlN1c3BlbnNlIGZhbGxiYWNrPXs8TG9hZGluZ01hc2NvdCAvPn0+PExvZ2luIExvZ2luQnV0dG9uPXtMb2dpbkJ1dHRvbn0gLz48L1JlYWN0LlN1c3BlbnNlPlxuICAgICAgICAgICAgICAgICAgICAgICAgOiA8TG9naW5CdXR0b24gaGFuZGxlQ2xpY2s9e2xhenlsb2FkTG9naW59IC8+XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICA8Lz5cbiAgICAgICAgICAgICl9XG4gICAgICAgIDwvZGl2PlxuICAgICk7XG59XG5cbmV4cG9ydCBkZWZhdWx0IGZ1bmN0aW9uIEhlYWRlcihwcm9wcykge1xuICAgIGNvbnN0IHsgdXNlcm5hbWUgfSA9IHByb3BzO1xuXG4gICAgcmV0dXJuIChcbiAgICAgICAgPD5cbiAgICAgICAgICAgIDxUb3BOYXYgLz5cbiAgICAgICAgICAgIDxIZWFkZXJVc2VyIHVzZXJuYW1lPXt1c2VybmFtZX0gLz5cbiAgICAgICAgICAgIDxTZWFyY2ggLz5cbiAgICAgICAgPC8+XG4gICAgKTtcbn1cbiIsIi8qIGVzbGludC1kaXNhYmxlIHJlYWN0L2J1dHRvbi1oYXMtdHlwZSAqL1xuaW1wb3J0IFJlYWN0IGZyb20gJ3JlYWN0JztcbmltcG9ydCB7IEJpU2VhcmNoIH0gZnJvbSAncmVhY3QtaWNvbnMvYmknO1xuaW1wb3J0IE1vZGFsIGZyb20gJy4uL3VpL01vZGFsLmpzeCc7XG5pbXBvcnQgQnV0dG9uIGZyb20gJy4uL3VpL0J1dHRvbi5qc3gnO1xuXG5jb25zdCBBUElfRU5EUE9JTlQgPSBwcm9jZXNzLmVudi5BUElfRU5EUE9JTlRfU0VBUkNIO1xuXG5leHBvcnQgZGVmYXVsdCBmdW5jdGlvbiBTZWFyY2goKSB7XG4gICAgY29uc3QgW3NlYXJjaFRlcm0sIHNldFNlYXJjaFRlcm1dID0gUmVhY3QudXNlU3RhdGUoJycpO1xuXG4gICAgY29uc3QgcmVzdWx0c0luaXRpYWxTdGF0ZSA9IHtcbiAgICAgICAgaGl0czogW10sXG4gICAgICAgIGlzTG9hZGluZzogZmFsc2UsXG4gICAgICAgIGlzRXJyb3I6IGZhbHNlLFxuICAgIH07XG4gICAgY29uc3QgcmVzdWx0c1JlZHVjZXIgPSAoc3RhdGUsIGFjdGlvbikgPT4ge1xuICAgICAgICBzd2l0Y2ggKGFjdGlvbi50eXBlKSB7XG4gICAgICAgICAgICBjYXNlICdTRUFSQ0hfRkVUQ0hfSU5JVCc6XG4gICAgICAgICAgICAgICAgcmV0dXJuIHtcbiAgICAgICAgICAgICAgICAgICAgLi4uc3RhdGUsXG4gICAgICAgICAgICAgICAgICAgIGlzTG9hZGluZzogdHJ1ZSxcbiAgICAgICAgICAgICAgICAgICAgaXNFcnJvcjogZmFsc2UsXG4gICAgICAgICAgICAgICAgfTtcbiAgICAgICAgICAgIGNhc2UgJ1NFQVJDSF9GRVRDSF9TVUNDRVNTJzpcbiAgICAgICAgICAgICAgICByZXR1cm4ge1xuICAgICAgICAgICAgICAgICAgICAuLi5zdGF0ZSxcbiAgICAgICAgICAgICAgICAgICAgaXNMb2FkaW5nOiBmYWxzZSxcbiAgICAgICAgICAgICAgICAgICAgaXNFcnJvcjogZmFsc2UsXG4gICAgICAgICAgICAgICAgICAgIGhpdHM6IGFjdGlvbi5wYXlsb2FkLFxuICAgICAgICAgICAgICAgIH07XG4gICAgICAgICAgICBjYXNlICdTRUFSQ0hfRkVUQ0hfRkFJTCc6XG4gICAgICAgICAgICAgICAgcmV0dXJuIHtcbiAgICAgICAgICAgICAgICAgICAgLi4uc3RhdGUsXG4gICAgICAgICAgICAgICAgICAgIGlzTG9hZGluZzogZmFsc2UsXG4gICAgICAgICAgICAgICAgICAgIGlzRXJyb3I6IHRydWUsXG4gICAgICAgICAgICAgICAgfTtcbiAgICAgICAgICAgIGNhc2UgJ1JFU0VUJzpcbiAgICAgICAgICAgICAgICByZXR1cm4ge1xuICAgICAgICAgICAgICAgICAgICAuLi5zdGF0ZSxcbiAgICAgICAgICAgICAgICAgICAgaXNMb2FkaW5nOiBmYWxzZSxcbiAgICAgICAgICAgICAgICAgICAgaXNFcnJvcjogZmFsc2UsXG4gICAgICAgICAgICAgICAgICAgIGhpdHM6IFtdLFxuICAgICAgICAgICAgICAgIH07XG4gICAgICAgICAgICBkZWZhdWx0OlxuICAgICAgICAgICAgICAgIHRocm93IG5ldyBFcnJvcigpO1xuICAgICAgICB9XG4gICAgfTtcbiAgICAvLyBjYWxsIGBkaXNwYXRjaFJlc3VsdHNgIHRvIGNoYW5nZSBgcmVzdWx0c2Agb2JqZWN0XG4gICAgY29uc3QgW3Jlc3VsdHMsIGRpc3BhdGNoUmVzdWx0c10gPSBSZWFjdC51c2VSZWR1Y2VyKHJlc3VsdHNSZWR1Y2VyLCByZXN1bHRzSW5pdGlhbFN0YXRlKTtcblxuICAgIGNvbnN0IGhhbmRsZVNlYXJjaCA9IChldmVudCkgPT4ge1xuICAgICAgICBzZXRTZWFyY2hUZXJtKGV2ZW50LnRhcmdldC52YWx1ZSk7XG4gICAgfTtcblxuICAgIFJlYWN0LnVzZUVmZmVjdCgoKSA9PiB7XG4gICAgICAgIGlmICghc2VhcmNoVGVybSkge1xuICAgICAgICAgICAgZGlzcGF0Y2hSZXN1bHRzKHsgdHlwZTogJ1JFU0VUJyB9KTtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmIChzZWFyY2hUZXJtLmxlbmd0aCA8IDMpIHtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIE1hcmsgc2VhcmNoIGZvcm0gYXMgaW5pdGlhbGl6aW5nL2xvYWRpbmdcbiAgICAgICAgZGlzcGF0Y2hSZXN1bHRzKHsgdHlwZTogJ1NFQVJDSF9GRVRDSF9JTklUJyB9KTtcblxuICAgICAgICAvLyBGZXRjaCBmcm9tIEFQSVxuICAgICAgICBjb25zdCB1cmwgPSBBUElfRU5EUE9JTlQgKyBzZWFyY2hUZXJtO1xuICAgICAgICBmZXRjaCh1cmwpXG4gICAgICAgICAgICAudGhlbigocmVzcG9uc2UpID0+IHJlc3BvbnNlLmpzb24oKSlcbiAgICAgICAgICAgIC50aGVuKChyZXN1bHQpID0+IHtcbiAgICAgICAgICAgICAgICBkaXNwYXRjaFJlc3VsdHMoe1xuICAgICAgICAgICAgICAgICAgICB0eXBlOiAnU0VBUkNIX0ZFVENIX1NVQ0NFU1MnLFxuICAgICAgICAgICAgICAgICAgICBwYXlsb2FkOiByZXN1bHQuY29sbGVjdGlvbi5pdGVtcyxcbiAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgIH0pXG4gICAgICAgICAgICAuY2F0Y2goKCkgPT4gZGlzcGF0Y2hSZXN1bHRzKHsgdHlwZTogJ1NFQVJDSF9GRVRDSF9GQUlMJyB9KSk7XG4gICAgfSwgW3NlYXJjaFRlcm1dKTtcblxuICAgIGNvbnN0IFtvcGVuLCBzZXRPcGVuXSA9IFJlYWN0LnVzZVN0YXRlKGZhbHNlKTtcbiAgICBjb25zdCBoYW5kbGVDbG9zZSA9ICgpID0+IHtcbiAgICAgICAgc2V0T3BlbihmYWxzZSk7XG4gICAgfTtcblxuICAgIHJldHVybiAoXG4gICAgICAgIDxkaXYgaWQ9XCJzZWFyY2hcIj5cbiAgICAgICAgICAgIDxCdXR0b24gY2xhc3Nlcz17eyAnYnV0dG9uLWhlYWRlcic6IHRydWUgfX0gb25DbGljaz17KCkgPT4gc2V0T3Blbih0cnVlKX0+XG4gICAgICAgICAgICAgICAgPEJpU2VhcmNoIHNpemU9XCIyOFwiIHRpdGxlPVwiU2VhcmNoXCIgLz5cbiAgICAgICAgICAgIDwvQnV0dG9uPlxuICAgICAgICAgICAgPE1vZGFsIG9wZW49e29wZW59IGNsb3NlPXtoYW5kbGVDbG9zZX0gY2xvc2VUYWJJbmRleD1cIjBcIj5cbiAgICAgICAgICAgICAgICA8ZGl2PlxuICAgICAgICAgICAgICAgICAgICA8aW5wdXQgaWQ9XCJzZWFyY2hmb3JtXCIgdHlwZT1cInRleHRcIiB2YWx1ZT17c2VhcmNoVGVybX0gcGxhY2Vob2xkZXI9XCJTZWFyY2ggYWxsIHRoZSB0aGluZ3NcIiB0YWJJbmRleD1cIjBcIiBvbkNoYW5nZT17aGFuZGxlU2VhcmNofSByZWY9eyhpbnB1dCkgPT4gaW5wdXQgJiYgaW5wdXQuZm9jdXMoKX0gLz5cbiAgICAgICAgICAgICAgICAgICAgeycgJ31cbiAgICAgICAgICAgICAgICAgICAgPGJ1dHRvbiB0eXBlPVwicmVzZXRcIiB0YWJJbmRleD1cIjBcIiBvbkNsaWNrPXsoKSA9PiBzZXRTZWFyY2hUZXJtKCcnKX0+UmVzZXQ8L2J1dHRvbj5cblxuICAgICAgICAgICAgICAgICAgICB7cmVzdWx0cy5pc0Vycm9yICYmIDxwPlNvbWV0aGluZyB3ZW50IHdyb25nPC9wPn1cblxuICAgICAgICAgICAgICAgICAgICB7cmVzdWx0cy5pc0xvYWRpbmcgPyAoPHA+TG9hZGluZy4uLjwvcD4pIDogKDxTZWFyY2hSZXN1bHRzIHJlc3VsdHM9e3Jlc3VsdHN9IC8+KX1cbiAgICAgICAgICAgICAgICA8L2Rpdj5cbiAgICAgICAgICAgIDwvTW9kYWw+XG4gICAgICAgIDwvZGl2PlxuICAgICk7XG59XG5cbmZ1bmN0aW9uIFNlYXJjaFJlc3VsdHMocHJvcHMpIHtcbiAgICBjb25zdCB7IHJlc3VsdHMgfSA9IHByb3BzO1xuXG4gICAgaWYgKHJlc3VsdHMuaGl0cy5sZW5ndGggPT09IDApIHtcbiAgICAgICAgcmV0dXJuIDxwPk5vIHJlc3VsdHMgZm91bmQ8L3A+O1xuICAgIH1cblxuICAgIHJldHVybiAoXG4gICAgICAgIDx1bD5cbiAgICAgICAgICAgIHtyZXN1bHRzLmhpdHMubWFwKChpdGVtKSA9PiA8U2VhcmNoUmVzdWx0IGtleT17aXRlbS50aXRsZV9zb3J0fSBpdGVtPXtpdGVtfSAvPil9XG4gICAgICAgIDwvdWw+XG4gICAgKTtcbn1cblxuLyoqXG4gKiBJdGVtIGNvbXBvbmVudFxuICogQHBhcmFtIHtPYmplY3R9IHByb3BzLml0ZW0gSXRlbSBvYmplY3RcbiAqIEBwYXJhbSB7fSBvblJlbW92ZUl0ZW1cbiAqL1xuZnVuY3Rpb24gU2VhcmNoUmVzdWx0KHByb3BzKSB7XG4gICAgY29uc3QgeyBpdGVtIH0gPSBwcm9wcztcblxuICAgIHJldHVybiAoXG4gICAgICAgIDxsaT5cbiAgICAgICAgICAgIDxhIGhyZWY9e2l0ZW0ubGlua3MucGFnZX0+XG4gICAgICAgICAgICAgICAgPGRmbj57aXRlbS50aXRsZX08L2Rmbj5cbiAgICAgICAgICAgICAgICB7JyAnfVxuICAgICAgICAgICAgICAgIDxzcGFuPlxuICAgICAgICAgICAgICAgICAgICAoXG4gICAgICAgICAgICAgICAgICAgIHtpdGVtLnR5cGV9XG4gICAgICAgICAgICAgICAgICAgIClcbiAgICAgICAgICAgICAgICA8L3NwYW4+XG4gICAgICAgICAgICA8L2E+XG4gICAgICAgIDwvbGk+XG4gICAgKTtcbn1cblxuLy8gUmVhY3RET00ucmVuZGVyKFxuLy8gICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoU2VhcmNoKSxcbi8vICAgICBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnc2VhcmNoJyksXG4vLyApO1xuIiwiaW1wb3J0IFJlYWN0IGZyb20gJ3JlYWN0JztcbmltcG9ydCB7IENTU1RyYW5zaXRpb24gfSBmcm9tICdyZWFjdC10cmFuc2l0aW9uLWdyb3VwJztcbmltcG9ydCBOYXZNZW51IGZyb20gJy4uL3VpL05hdk1lbnUuanN4JztcbmltcG9ydCBCdXR0b24gZnJvbSAnLi4vdWkvQnV0dG9uLmpzeCc7XG5cbmV4cG9ydCBkZWZhdWx0IGZ1bmN0aW9uIFRvcE5hdigpIHtcbiAgICBjb25zdCBbb3Blbiwgc2V0T3Blbl0gPSBSZWFjdC51c2VTdGF0ZShmYWxzZSk7XG4gICAgY29uc3QgdG9nZ2xlT3BlbiA9ICgpID0+IHtcbiAgICAgICAgc2V0T3Blbighb3Blbik7XG4gICAgfTtcblxuICAgIGNvbnN0IGJ1dHRvbkNsYXNzZXMgPSB7XG4gICAgICAgIGFjdGl2ZTogb3BlbixcbiAgICAgICAgaW5hY3RpdmU6ICFvcGVuLFxuICAgIH07XG5cbiAgICByZXR1cm4gKFxuICAgICAgICA8Q1NTVHJhbnNpdGlvbiBpbj17b3Blbn0gdGltZW91dD17MTUwMH0+XG4gICAgICAgICAgICA8TmF2TWVudT5cbiAgICAgICAgICAgICAgICA8TmF2TWVudS5JdGVtIHNlbGVjdGVkPlxuICAgICAgICAgICAgICAgICAgICA8aDY+PGEgaHJlZj1cIi9nYW1lc1wiPlN0YXJ0IEdhbWU8L2E+PC9oNj5cbiAgICAgICAgICAgICAgICA8L05hdk1lbnUuSXRlbT5cbiAgICAgICAgICAgICAgICA8TmF2TWVudS5JdGVtPlxuICAgICAgICAgICAgICAgICAgICB7LyogYWNjZXNzaWJpbGl6ZSAqL31cbiAgICAgICAgICAgICAgICAgICAgPEJ1dHRvbiByb2xlPVwic3dpdGNoXCIgYXJpYS1jaGVja2VkPXtvcGVufSBpZD1cIm1lbnVcIiBjbGFzc2VzPXtidXR0b25DbGFzc2VzfSBvbkNsaWNrPXt0b2dnbGVPcGVufT5cbiAgICAgICAgICAgICAgICAgICAgICAgIE9wdGlvbnNcbiAgICAgICAgICAgICAgICAgICAgPC9CdXR0b24+XG4gICAgICAgICAgICAgICAgPC9OYXZNZW51Lkl0ZW0+XG4gICAgICAgICAgICAgICAgPE5hdk1lbnUuSXRlbSBjbGFzc05hbWU9XCJoaWRkZW5cIj48YSBocmVmPVwiL2dhbWVzXCI+R2FtZXM8L2E+PC9OYXZNZW51Lkl0ZW0+XG4gICAgICAgICAgICAgICAgPE5hdk1lbnUuSXRlbSBjbGFzc05hbWU9XCJoaWRkZW5cIj48YSBocmVmPVwiL3Blb3BsZVwiPlBlb3BsZTwvYT48L05hdk1lbnUuSXRlbT5cbiAgICAgICAgICAgICAgICA8TmF2TWVudS5JdGVtIGNsYXNzTmFtZT1cImhpZGRlblwiPjxhIGhyZWY9XCIvbXVzaWNcIj5NdXNpYzwvYT48L05hdk1lbnUuSXRlbT5cbiAgICAgICAgICAgIDwvTmF2TWVudT5cbiAgICAgICAgPC9DU1NUcmFuc2l0aW9uPlxuICAgICk7XG59XG4iLCJpbXBvcnQgUmVhY3QgZnJvbSAncmVhY3QnO1xuaW1wb3J0IGNuIGZyb20gJ2NsYXNzbmFtZXMnO1xuaW1wb3J0IFByb3BUeXBlcyBmcm9tICdwcm9wLXR5cGVzJztcblxuZnVuY3Rpb24gQnV0dG9uKHsgY29sb3IsIGNsYXNzZXMsIHNpemUsIHR5cGUsIHZhcmlhbnQsIGNoaWxkcmVuLCAuLi5wcm9wcyB9KSB7XG4gICAgY29uc3QgY2xhc3NOYW1lID0gY24oe1xuICAgICAgICAuLi5jbGFzc2VzLFxuICAgICAgICBbYGJ1dHRvbi1jb2xvci0ke2NvbG9yfWBdOiBjb2xvcixcbiAgICAgICAgW2BidXR0b24tc2l6ZS0ke3NpemV9YF06IHNpemUsXG4gICAgICAgIFtgYnV0dG9uLSR7dmFyaWFudH1gXTogdmFyaWFudCxcbiAgICB9KTtcblxuICAgIHJldHVybiAoXG4gICAgICAgIDxidXR0b24gY2xhc3NOYW1lPXtjbGFzc05hbWV9IHR5cGU9e3R5cGV9IHsuLi5wcm9wc30+XG4gICAgICAgICAgICB7Y2hpbGRyZW59XG4gICAgICAgIDwvYnV0dG9uPlxuICAgICk7XG59XG5cbkJ1dHRvbi5wcm9wVHlwZXMgPSB7XG4gICAgY29sb3I6IFByb3BUeXBlcy5vbmVPZihbJ2RlZmF1bHQnLCAncHJpbWFyeScsICdyZWQnLCAnZ3JlZW4nLCAnZGFyaycsICdsaWdodCddKSxcbiAgICBjbGFzc2VzOiBQcm9wVHlwZXMuYW55LFxuICAgIHNpemU6IFByb3BUeXBlcy5vbmVPZihbJ3NtYWxsJywgJ21lZGl1bScsICdsYXJnZSddKSxcbiAgICB0eXBlOiBQcm9wVHlwZXMub25lT2YoWydidXR0b24nLCAnc3VibWl0JywgJ3Jlc2V0J10pLFxuICAgIHZhcmlhbnQ6IFByb3BUeXBlcy5vbmVPZihbJ3RleHQnLCAnY29udGFpbmVkJywgJ291dGxpbmVkJywgJ2xpbmsnLCAnY2xvc2UnXSksXG4gICAgY2hpbGRyZW46IFByb3BUeXBlcy5ub2RlLFxufTtcblxuQnV0dG9uLmRlZmF1bHRQcm9wcyA9IHtcbiAgICB0eXBlOiAnYnV0dG9uJyxcbiAgICB2YXJpYW50OiAndGV4dCcsXG59O1xuXG5leHBvcnQgZGVmYXVsdCBCdXR0b247XG4iLCJpbXBvcnQgUmVhY3QgZnJvbSAncmVhY3QnO1xuaW1wb3J0IHsgQ1NTVHJhbnNpdGlvbiB9IGZyb20gJ3JlYWN0LXRyYW5zaXRpb24tZ3JvdXAnO1xuaW1wb3J0IHsgQmlYIH0gZnJvbSAncmVhY3QtaWNvbnMvYmknO1xuXG5leHBvcnQgZGVmYXVsdCBmdW5jdGlvbiBNb2RhbCh7XG4gICAgY2hpbGRyZW4sXG4gICAgb3BlbiA9IHRydWUsXG4gICAgY2xvc2UgPSBudWxsLFxuICAgIHRpbWVvdXQgPSA1MDAsXG4gICAgb3ZlcmxheSA9IHRydWUsXG4gICAgY2xvc2VCdXR0b24gPSB0cnVlLFxufSkge1xuICAgIGNvbnN0IENsb3NlQnV0dG9uID0gKCkgPT4gY2xvc2VCdXR0b24gJiYgKFxuICAgICAgICA8YnV0dG9uIHR5cGU9XCJidXR0b25cIiByb2xlPVwic3dpdGNoXCIgYXJpYS1jaGVja2VkPXtvcGVufSBhcmlhLWxhYmVsPVwiQ2xvc2VcIiBjbGFzc05hbWU9XCJtb2RhbC1jbG9zZSBidXR0b24tY2xvc2VcIiBvbkNsaWNrPXtjbG9zZX0+XG4gICAgICAgICAgICA8QmlYIGFyaWFsLWhpZGRlbj1cInRydWVcIiAvPlxuICAgICAgICA8L2J1dHRvbj5cbiAgICApO1xuICAgIGNvbnN0IE92ZXJsYXkgPSAoKSA9PiBvdmVybGF5ICYmIChcbiAgICAgICAgPGRpdiBjbGFzc05hbWU9XCJtb2RhbC1vdmVybGF5XCIgcm9sZT1cImJ1dHRvblwiIG9uQ2xpY2s9e2Nsb3NlfSBhcmlhLWhpZGRlbj1cInRydWVcIiBhcmlhLWxhYmVsPVwiY2xvc2VcIiAvPlxuICAgICk7XG5cbiAgICByZXR1cm4gKFxuICAgICAgICA8Q1NTVHJhbnNpdGlvbiBpbj17b3Blbn0gdGltZW91dD17dGltZW91dH0gY2xhc3NOYW1lcz1cIm1vZGFsXCIgdW5tb3VudE9uRXhpdD5cbiAgICAgICAgICAgIDxkaXYgY2xhc3NOYW1lPVwibW9kYWwgbW9kYWwtY29udGFpbmVyXCI+XG4gICAgICAgICAgICAgICAgPE92ZXJsYXkgLz5cbiAgICAgICAgICAgICAgICA8ZGl2IGNsYXNzTmFtZT1cIm1vZGFsLWNvbnRlbnQgbGlnaHRcIj5cbiAgICAgICAgICAgICAgICAgICAge2NoaWxkcmVufVxuICAgICAgICAgICAgICAgICAgICA8Q2xvc2VCdXR0b24gLz5cbiAgICAgICAgICAgICAgICA8L2Rpdj5cbiAgICAgICAgICAgIDwvZGl2PlxuICAgICAgICA8L0NTU1RyYW5zaXRpb24+XG4gICAgKTtcbn1cbiIsImltcG9ydCBSZWFjdCBmcm9tICdyZWFjdCc7XG5pbXBvcnQgUHJvcFR5cGVzIGZyb20gJ3Byb3AtdHlwZXMnO1xuaW1wb3J0IGNuIGZyb20gJ2NsYXNzbmFtZXMnO1xuaW1wb3J0IG1hdGNoQ29tcG9uZW50IGZyb20gJy4uLy4uL2xpYi9tYXRjaC1jb21wb25lbnQuanMnO1xuXG5jb25zdCBpc05hdk1lbnVJdGVtID0gbWF0Y2hDb21wb25lbnQoTmF2TWVudUl0ZW0pO1xuXG4vKipcbiAqIFVJIGNvbXBvbmVudCB0aGF0IGVtdWxhdGVzIGEgdmlkZW9nYW1lIHNlbGVjdC90aXRsZSBzY3JlZW4uIEEgc2VsZWN0ZWQgaXRlbSBpcyBoaWdobGlnaHRlZCB3aXRoXG4gKiBhIGNhcmV0LlxuICovXG5mdW5jdGlvbiBOYXZNZW51KHsgY2xhc3NOYW1lLCBjaGlsZHJlbiwgLi4ucHJvcHMgfSkge1xuICAgIGxldCBpbml0U2VsZWN0ZWQ7XG4gICAgUmVhY3QuQ2hpbGRyZW4uZm9yRWFjaChjaGlsZHJlbiwgKGNoaWxkLCBpbmRleCkgPT4ge1xuICAgICAgICBpZiAoY2hpbGQucHJvcHMuc2VsZWN0ZWQpIHtcbiAgICAgICAgICAgIGluaXRTZWxlY3RlZCA9IGluZGV4O1xuICAgICAgICB9XG4gICAgfSk7XG5cbiAgICAvLyBTZXQgdG8gaW5kZXggb2YgPE5hdk1lbnUuSXRlbT4gY2hpbGRcbiAgICBjb25zdCBbc2VsZWN0ZWQsIHNldFNlbGVjdGVkXSA9IFJlYWN0LnVzZVN0YXRlKGluaXRTZWxlY3RlZCk7XG5cbiAgICBjb25zdCBjbGFzc05hbWVzID0gY24oY2xhc3NOYW1lLCB7XG4gICAgICAgIG5hdm1lbnU6IHRydWUsXG4gICAgfSk7XG5cbiAgICAvLyBGaW5kIGZpcnN0IDxOYXZNZW51SXRlbSAvPiBjaGlsZCB0byBpbnNlcnQgdGFiSW5kZXggcHJvcFxuICAgIGxldCBmaXJzdFZhbGlkQ2hpbGQ7XG4gICAgbGV0IGNoaWxkUHJvcHM7XG5cbiAgICByZXR1cm4gKFxuICAgICAgICA8bmF2IGNsYXNzTmFtZT17Y2xhc3NOYW1lc30gey4uLnByb3BzfT5cbiAgICAgICAgICAgIHtSZWFjdC5DaGlsZHJlbi5tYXAoY2hpbGRyZW4sIChjaGlsZCwgaW5kZXgpID0+IHtcbiAgICAgICAgICAgICAgICBpZiAoIVJlYWN0LmlzVmFsaWRFbGVtZW50KGNoaWxkKSkge1xuICAgICAgICAgICAgICAgICAgICByZXR1cm4gY2hpbGQ7XG4gICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgaWYgKGlzTmF2TWVudUl0ZW0oY2hpbGQpKSB7XG4gICAgICAgICAgICAgICAgICAgIGZpcnN0VmFsaWRDaGlsZCA9IGZpcnN0VmFsaWRDaGlsZCB8fCBpbmRleDtcbiAgICAgICAgICAgICAgICAgICAgY2hpbGRQcm9wcyA9IHtcbiAgICAgICAgICAgICAgICAgICAgICAgIC4uLmNoaWxkLnByb3BzLFxuICAgICAgICAgICAgICAgICAgICAgICAgaW5kZXgsXG4gICAgICAgICAgICAgICAgICAgICAgICBzZXRTZWxlY3RlZCxcbiAgICAgICAgICAgICAgICAgICAgICAgIHNlbGVjdGVkOiBzZWxlY3RlZCA9PT0gaW5kZXgsXG4gICAgICAgICAgICAgICAgICAgICAgICB0YWJJbmRleDogZmlyc3RWYWxpZENoaWxkID09PSBpbmRleCA/IDAgOiAtMSxcbiAgICAgICAgICAgICAgICAgICAgfTtcblxuICAgICAgICAgICAgICAgICAgICByZXR1cm4gUmVhY3QuY2xvbmVFbGVtZW50KGNoaWxkLCBjaGlsZFByb3BzKTtcbiAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICByZXR1cm4gY2hpbGQ7XG4gICAgICAgICAgICB9KX1cbiAgICAgICAgPC9uYXY+XG4gICAgKTtcbn1cbk5hdk1lbnUucHJvcFR5cGVzID0ge1xuICAgIGNsYXNzTmFtZTogUHJvcFR5cGVzLnN0cmluZyxcbiAgICBjaGlsZHJlbjogUHJvcFR5cGVzLm5vZGUuaXNSZXF1aXJlZCxcbn07XG5OYXZNZW51LmRlZmF1bHRQcm9wcyA9IHtcbiAgICBjbGFzc05hbWU6ICcnLFxufTtcblxuZnVuY3Rpb24gTmF2TWVudUl0ZW0oe1xuICAgIGluZGV4LFxuICAgIGNhcmV0LFxuICAgIHNlbGVjdGVkLFxuICAgIHNldFNlbGVjdGVkLFxuICAgIGNsYXNzTmFtZSxcbiAgICBjaGlsZHJlbixcbiAgICAuLi5wcm9wc1xufSkge1xuICAgIGNvbnN0IGhhbmRsZUNsaWNrID0gKGV2ZW50KSA9PiB7XG4gICAgICAgIHNldFNlbGVjdGVkKGluZGV4KTtcbiAgICB9O1xuXG4gICAgY29uc3QgY2xhc3NOYW1lcyA9IGNuKGNsYXNzTmFtZSwge1xuICAgICAgICAnbmF2bWVudS1pdGVtJzogdHJ1ZSxcbiAgICAgICAgc2VsZWN0ZWQsXG4gICAgfSk7XG5cbiAgICByZXR1cm4gKFxuICAgICAgICA8ZGl2IGNsYXNzTmFtZT17Y2xhc3NOYW1lc30gcm9sZT1cIm1lbnVpdGVtXCIgb25DbGljaz17aGFuZGxlQ2xpY2t9IGFyaWEtaGlkZGVuPVwidHJ1ZVwiIHsuLi5wcm9wc30+XG4gICAgICAgICAgICB7Y2FyZXQgJiYgPGRpdiBjbGFzc05hbWU9XCJuYXZtZW51LWNhcmV0XCI+Jmd0OyZuYnNwOzwvZGl2Pn1cbiAgICAgICAgICAgIDxkaXYgY2xhc3NOYW1lPVwibmF2bWVudS1pdGVtLWNvbnRlbnRcIj57Y2hpbGRyZW59PC9kaXY+XG4gICAgICAgIDwvZGl2PlxuICAgICk7XG59XG5OYXZNZW51SXRlbS5wcm9wVHlwZXMgPSB7XG4gICAgaW5kZXg6IFByb3BUeXBlcy5udW1iZXIsXG4gICAgLyoqIFByZXBlbmQgYSBjYXJldCBvbiB0aGUgbWVudSBpdGVtICovXG4gICAgY2FyZXQ6IFByb3BUeXBlcy5ib29sLFxuICAgIC8qKiBEZXRlcm1pbmVzIGlmIHRoZSBjYXJldCBpcyB2aXNpYmxlIG9uIGluaXRpYWwgcmVuZGVyLiAqL1xuICAgIHNlbGVjdGVkOiBQcm9wVHlwZXMuYm9vbCxcbiAgICBzZXRTZWxlY3RlZDogUHJvcFR5cGVzLmZ1bmMsXG4gICAgY2xhc3NOYW1lOiBQcm9wVHlwZXMuc3RyaW5nLFxuICAgIGNoaWxkcmVuOiBQcm9wVHlwZXMubm9kZSxcbn07XG5OYXZNZW51SXRlbS5kZWZhdWx0UHJvcHMgPSB7XG4gICAgY2FyZXQ6IHRydWUsXG4gICAgc2VsZWN0ZWQ6IGZhbHNlLFxuICAgIGNsYXNzTmFtZTogJycsXG59O1xuXG5OYXZNZW51Lkl0ZW0gPSBOYXZNZW51SXRlbTtcblxuZXhwb3J0IGRlZmF1bHQgTmF2TWVudTtcbiIsIi8vIEVudHJ5IHBvaW50IGZvciBSZWFjdCBjb21wb25lbnRzIG9uIGFsbCBwYWdlc1xuXG5pbXBvcnQgUmVhY3QgZnJvbSAncmVhY3QnO1xuaW1wb3J0IFJlYWN0RE9NIGZyb20gJ3JlYWN0LWRvbSc7XG5cbi8vIFN0eWxlc2hlZXRzIHRoYXQgZ2V0IGluamVjdGVkIGludG8gPGhlYWQ+XG5pbXBvcnQgJ25vcm1hbGl6ZS5jc3MnO1xuaW1wb3J0ICcuLi9zdHlsZXMvYXBwLnNjc3MnO1xuXG4vLyBDb21wb25lbnRzIHRvIHJlbmRlclxuaW1wb3J0IENvbG9waG9uIGZyb20gJy4vY29tcG9uZW50cy9sYXlvdXQvQ29sb3Bob24uanN4JztcbmltcG9ydCBIZWFkZXIgZnJvbSAnLi9jb21wb25lbnRzL2xheW91dC9IZWFkZXIuanN4JztcblxuLy8gR3JhYiBkYXRhLSogcHJvcGVydGllcyBmcm9tIDxoZWFkZXI+IGVsZW1lbnQgYW5kIHBhc3MgdGhlbSBhcyBwcm9wcyB0byA8SGVhZGVyPiBjb21wb25lbnRcbmNvbnN0IGhlYWRlckVsZW1lbnQgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnaGVhZGVyJyk7XG5SZWFjdERPTS5yZW5kZXIoUmVhY3QuY3JlYXRlRWxlbWVudChIZWFkZXIsIHsuLi5oZWFkZXJFbGVtZW50LmRhdGFzZXR9KSwgaGVhZGVyRWxlbWVudCk7XG5cblJlYWN0RE9NLnJlbmRlcihcbiAgICBSZWFjdC5jcmVhdGVFbGVtZW50KENvbG9waG9uKSxcbiAgICBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnY29sb3Bob24nKSxcbik7XG5cbi8vIFJvdXRlclxuXG4vLyBjb25zdCBlbGVtZW50ID0gKFxuLy8gICAgIDw+XG4vLyAgICAgICAgIDxSb3V0ZXI+XG4vLyAgICAgICAgICAgICA8UGFnZSAvPlxuLy8gICAgICAgICA8L1JvdXRlcj5cbi8vICAgICA8Lz5cbi8vICk7XG5cbi8vIFJlYWN0RE9NLnJlbmRlcihlbGVtZW50LCBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncm9vdCcpKTtcbiIsImltcG9ydCBSZWFjdCBmcm9tICdyZWFjdCc7XG5cbmltcG9ydCBxdWVzdGlvbmJsb2NrIGZyb20gJy4uLy4uL2ltYWdlcy9pY29ucy9xdWVzdGlvbmJsb2NrLnBuZyc7XG5pbXBvcnQgbG9hZGluZ21hc2NvdCBmcm9tICcuLi8uLi9pbWFnZXMvaWNvbnMvbG9hZGluZ19tYXNjb3QuZ2lmJztcblxuZXhwb3J0IGZ1bmN0aW9uIFF1ZXN0aW9uQmxvY2soeyBjbGFzc05hbWU6IGNsYXNzTmFtZVByb3AsIC4uLnByb3BzIH0pIHtcbiAgICBjb25zdCBjbGFzc05hbWUgPSBgaWNvbiAke2NsYXNzTmFtZVByb3B9YDtcbiAgICByZXR1cm4gUmVhY3QuY3JlYXRlRWxlbWVudCgnaW1nJywge1xuICAgICAgICAuLi5wcm9wcywgc3JjOiBxdWVzdGlvbmJsb2NrLCBhbHQ6ICdbP10nLCBjbGFzc05hbWUsXG4gICAgfSk7XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBMb2FkaW5nTWFzY290KHsgY2xhc3NOYW1lOiBjbGFzc05hbWVQcm9wLCAuLi5wcm9wcyB9KSB7XG4gICAgY29uc3QgY2xhc3NOYW1lID0gYGljb24gJHtjbGFzc05hbWVQcm9wfWA7XG4gICAgcmV0dXJuIFJlYWN0LmNyZWF0ZUVsZW1lbnQoJ2ltZycsIHtcbiAgICAgICAgLi4ucHJvcHMsIHNyYzogbG9hZGluZ21hc2NvdCwgYWx0OiAnbG9hZGluZycsIGNsYXNzTmFtZSxcbiAgICB9KTtcbn1cblxuZXhwb3J0IGRlZmF1bHQgeyBRdWVzdGlvbkJsb2NrLCBMb2FkaW5nTWFzY290IH07XG4iLCJjb25zdCBtYXRjaENvbXBvbmVudCA9IChDb21wb25lbnQpID0+IChjKSA9PiB7XHJcbiAgICAvLyBSZWFjdCBDb21wb25lbnRcclxuICAgIGlmIChjLnR5cGUgPT09IENvbXBvbmVudCkge1xyXG4gICAgICAgIHJldHVybiB0cnVlO1xyXG4gICAgfVxyXG5cclxuICAgIC8vIE1hdGNoaW5nIGNvbXBvbmVudFR5cGVcclxuICAgIGlmIChjLnByb3BzICYmIGMucHJvcHMuY29tcG9uZW50VHlwZSA9PT0gQ29tcG9uZW50KSB7XHJcbiAgICAgICAgcmV0dXJuIHRydWU7XHJcbiAgICB9XHJcblxyXG4gICAgcmV0dXJuIGZhbHNlO1xyXG59O1xyXG5cclxuZXhwb3J0IGRlZmF1bHQgbWF0Y2hDb21wb25lbnQ7IiwiLy8gVXNhZ2U6XHJcbi8vIGlmIChzdG9yYWdlQXZhaWxhYmxlKCdsb2NhbFN0b3JhZ2UnKSkgey8qKiAqL31cclxuXHJcbmV4cG9ydCBkZWZhdWx0IGZ1bmN0aW9uIHN0b3JhZ2VBdmFpbGFibGUodHlwZSkge1xyXG4gICAgbGV0IHN0b3JhZ2U7XHJcbiAgICB0cnkge1xyXG4gICAgICAgIHN0b3JhZ2UgPSB3aW5kb3dbdHlwZV07XHJcbiAgICAgICAgY29uc3QgeCA9ICdfX3N0b3JhZ2VfdGVzdF9fJztcclxuICAgICAgICBzdG9yYWdlLnNldEl0ZW0oeCwgeCk7XHJcbiAgICAgICAgc3RvcmFnZS5yZW1vdmVJdGVtKHgpO1xyXG5cclxuICAgICAgICByZXR1cm4gdHJ1ZTtcclxuICAgIH0gY2F0Y2ggKGUpIHtcclxuICAgICAgICByZXR1cm4gZSBpbnN0YW5jZW9mIERPTUV4Y2VwdGlvbiAmJiAoXHJcbiAgICAgICAgICAgIC8vIGV2ZXJ5dGhpbmcgZXhjZXB0IEZpcmVmb3hcclxuICAgICAgICAgICAgZS5jb2RlID09PSAyMlxyXG4gICAgICAgICAgICAvLyBGaXJlZm94XHJcbiAgICAgICAgICAgIHx8IGUuY29kZSA9PT0gMTAxNFxyXG4gICAgICAgICAgICAvLyB0ZXN0IG5hbWUgZmllbGQgdG9vLCBiZWNhdXNlIGNvZGUgbWlnaHQgbm90IGJlIHByZXNlbnRcclxuICAgICAgICAgICAgLy8gZXZlcnl0aGluZyBleGNlcHQgRmlyZWZveFxyXG4gICAgICAgICAgICB8fCBlLm5hbWUgPT09ICdRdW90YUV4Y2VlZGVkRXJyb3InXHJcbiAgICAgICAgICAgIC8vIEZpcmVmb3hcclxuICAgICAgICAgICAgfHwgZS5uYW1lID09PSAnTlNfRVJST1JfRE9NX1FVT1RBX1JFQUNIRUQnKVxyXG4gICAgICAgICAgICAvLyBhY2tub3dsZWRnZSBRdW90YUV4Y2VlZGVkRXJyb3Igb25seSBpZiB0aGVyZSdzIHNvbWV0aGluZyBhbHJlYWR5IHN0b3JlZFxyXG4gICAgICAgICAgICAmJiAoc3RvcmFnZSAmJiBzdG9yYWdlLmxlbmd0aCAhPT0gMCk7XHJcbiAgICB9XHJcbn1cclxuIiwiLy8gZXh0cmFjdGVkIGJ5IG1pbmktY3NzLWV4dHJhY3QtcGx1Z2luXG5leHBvcnQge307Il0sInNvdXJjZVJvb3QiOiIifQ==