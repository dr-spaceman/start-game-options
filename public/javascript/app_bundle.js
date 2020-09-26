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

/***/ "./assets/images/footer_diorama.png":
/*!******************************************!*\
  !*** ./assets/images/footer_diorama.png ***!
  \******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAA+CAYAAABZcVnrAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAICSURBVHja7FgxUoQwFM1nGHtbrbyAnYN30NbGRk9gZS/0NnoCKxsvsEeAsdbWSlu7Lbb58uPCQDYJCQtJXPNnssznJfB4yX+BZYjIJmkPV7htX7asf4SWsCni8RqL7yN+5G0d7+eARVFgtx+1vdtn1u2ni5TNEeubv5zkvXxMJFOpd7f/sQFdvObSIav7S2aqYsICj0gwEhyMmyegAuE2YxhUIFQoNNaNguKNKO+ea3IDQrP5ILcZgUDrgwL5FWNoTHayrU7S3s4Y5nluvAXOt9UpQqpgSFWs2kmCIfgvFNymCLAsS2yCiuH3cvIwwiVFMtZm6GZkE1hV1QYoO2eDbz3FdIO6YZZlwGYOWwW5Yi6IjVLQZmpcK+hcORuCbUHIwMVi0cvrjyTtxYZwMYDvgfpp5R1U6gEATibXEq18sPG5QT8c7XsCbuOD2mmd2vda/DgzW4PrAeijKIYIeqtWIx/04XOmCganXJegVUHY+poVLikSICvR+dygkc7sg8D95q8GDmwzTnBVJ27t2QH6xhMl89ND/ZM5wlOp5IrBPvCUEu1AzzjQPPfOlJ9MJ79rPOkBDUhH2UAPeNomqqfyjG9OcWAR/6PeeYLBvyxAdy8EencKLI9rcOcJpuI3Q2h5T0FxVwkhj2swEowEbQhC9QWh5b0XVjoRWh7X4M4T/BFgACMtzIYbX1MTAAAAAElFTkSuQmCC");

/***/ }),

/***/ "./assets/images/h1_condensed.png":
/*!****************************************!*\
  !*** ./assets/images/h1_condensed.png ***!
  \****************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ("data:image/png;base64,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");

/***/ }),

/***/ "./assets/images/questionblock.png":
/*!*****************************************!*\
  !*** ./assets/images/questionblock.png ***!
  \*****************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAANCAMAAACq939wAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAAxQTFRF/9uStiQA/7YA////12/UCQAAAAR0Uk5T////AEAqqfQAAAA9SURBVHjaVI3RDgAgCAKB/v+fk3BZ9+C8gRMaFiQcSojebRGSNSyq1d1IrviI00iak+iVSb5aQz+8bAEGAEBqAMyCXqpGAAAAAElFTkSuQmCC");

/***/ }),

/***/ "./assets/images/twitter_sm.png":
/*!**************************************!*\
  !*** ./assets/images/twitter_sm.png ***!
  \**************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAbhJREFUeNqUk09LG0EYxp+dGbtZY6tpRKtREtGDhATUin+pB3PQD+AX8tZ+mJ56KlSE9qAHQQUll1KrtjaCiSaBLLvZ2XFmF3Z3JC31hWGGd+b5zfO+wxgfa/UR7os9AVHCM8KAcUaJUWEO5/tvzBfFnGXK5P+FkOO37ZRqjrvPHF8UKSWo2vZzDCBDGZSWeVzgzutGG3ufj/Dt4ALvVqdQ2Vr8K0BplJZ0fcAX8Tg4rSOVzQVzMt9rKC3r+j54UFUYzHoZzcl8r1BaCRABLQKkUtH6w/tPmmB5cQIblfkEQIC5sg4uYgI1Y0A6N6MBTr43sb4Zn1Xa0AF6O3ga20sT2tmeDpKA3Z28BrhutdERTxy4qolJQKKErz9uMDyaiQny7ZEEBE3kehPTAxYcL0x8OfwFv3sb7c0WUph7W4hLCB0I7bnKkxaO/4SVDkzqTfzZbqCMpAMJcLiP+46HQYsFyelhG6TvFap1A66nN3Ftaky67QTrpu1BaRmjpH714GbHJXjIomgQivxgCyu5NKhBNMBls4W2YHiwOW6aLpSW9ZtkQSaOz2v2a/2+f3+utEka8sKFRwEGADcq6GEi0lC0AAAAAElFTkSuQmCC");

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
/* harmony import */ var _Colophon_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Colophon.jsx */ "./assets/javascript/Colophon.jsx");
/* harmony import */ var _Header_jsx__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./Header.jsx */ "./assets/javascript/Header.jsx");
/* harmony import */ var normalize_css__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! normalize.css */ "./node_modules/normalize.css/normalize.css");
/* harmony import */ var normalize_css__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(normalize_css__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _styles_app_scss__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../styles/app.scss */ "./assets/styles/app.scss");
/* harmony import */ var _styles_app_scss__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_styles_app_scss__WEBPACK_IMPORTED_MODULE_5__);

 // Components to render


 // Stylesheets that get injected into <head>


 // Grab data-* properties from <header> element and pass them as props to <Header> component

const headerElement = document.getElementById('header');
react_dom__WEBPACK_IMPORTED_MODULE_1___default.a.render( /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Header_jsx__WEBPACK_IMPORTED_MODULE_3__["default"], headerElement.dataset), headerElement);
react_dom__WEBPACK_IMPORTED_MODULE_1___default.a.render( /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Colophon_jsx__WEBPACK_IMPORTED_MODULE_2__["default"]), document.getElementById('colophon'));

const Content = () => {
  const [open, setOpen] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState(true);
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("h1", null, "Hello World!"), open && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Lorem ipsum dolor sit amet ", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "#consectetur"
  }, "consectetur adipisicing elit"), ". Eveniet voluptas incidunt atque ipsam, nobis quis inventore, velit libero vel autem tempora, fugit soluta excepturi ", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "#foo"
  }, "voluptatum"), "! Soluta possimus nihil dolore hic."), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Lorem ipsum dolor sit amet consectetur adipisicing elit. Aperiam, repellendus ullam cumque sequi deserunt cum possimus, deleniti impedit pariatur atque eligendi. Eius debitis delectus maxime esse a, odio sint mollitia!"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "#bar"
  }, "Lorem ipsum dolor sit amet consectetur"), ", adipisicing elit. Quis tenetur facilis ipsum doloremque magni cum. Praesentium reiciendis vitae omnis ex sint eaque eos necessitatibus assumenda atque reprehenderit, commodi quod. Nam! Lorem ipsum dolor sit amet consectetur adipisicing elit. Obcaecati consectetur similique nulla veritatis a impedit provident eaque dignissimos facere soluta voluptate ab aliquam quidem culpa dolores hic excepturi, eius quae?"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Lorem ipsum dolor sit amet consectetur adipisicing elit. Alias facere magni culpa molestiae voluptates ducimus? Ducimus minus nesciunt tempora ad asperiores! Totam autem dolore eos delectus reprehenderit ipsa animi omnis."), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Lorem ipsum dolor sit, amet consectetur adipisicing elit. ", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "#baz"
  }, "Itaque beatae"), " eaque praesentium modi voluptates libero obcaecati earum? Officia impedit distinctio deleniti exercitationem delectus! Assumenda, hic a eaque nobis velit quis."), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Lorem ipsum dolor sit, amet consectetur adipisicing elit. Accusamus magni ut aliquam officiis nostrum consequatur tempore, at repudiandae, laudantium exercitationem itaque cum, et voluptate suscipit modi unde ad doloremque sit! Lorem ipsum dolor sit amet consectetur adipisicing elit. Autem maiores quisquam distinctio quos qui adipisci voluptates perferendis officia commodi, fugit eius est ut corrupti reprehenderit fuga quibusdam, cum itaque sequi?"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Lorem ipsum, dolor sit amet consectetur adipisicing elit. Aperiam deserunt ea natus iusto ipsa, labore in consectetur, beatae commodi voluptas hic, ratione asperiores dicta accusantium optio quas unde omnis error!"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Lorem ipsum dolor sit amet consectetur adipisicing elit. Ipsa maxime quod ex iure eius et, sint doloremque! Libero exercitationem pariatur hic dignissimos, dolorum consequuntur odio consectetur voluptate accusamus voluptatem a."), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Fin.")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    type: "button",
    onClick: () => setOpen(!open)
  }, "Toggle filler text"));
};

react_dom__WEBPACK_IMPORTED_MODULE_1___default.a.render( /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(Content, null), document.getElementById('content')); // const element = (
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

/***/ "./assets/javascript/Header.jsx":
/*!**************************************!*\
  !*** ./assets/javascript/Header.jsx ***!
  \**************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Header; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _NavMenu_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./NavMenu.jsx */ "./assets/javascript/NavMenu.jsx");
/* harmony import */ var _Login_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Login.jsx */ "./assets/javascript/Login.jsx");
/* harmony import */ var _Search_jsx__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./Search.jsx */ "./assets/javascript/Search.jsx");




function Header(props) {
  const {
    username
  } = props;
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_NavMenu_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], null));
}

/***/ }),

/***/ "./assets/javascript/Login.jsx":
/*!*************************************!*\
  !*** ./assets/javascript/Login.jsx ***!
  \*************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Login; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _Modal_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Modal.jsx */ "./assets/javascript/Modal.jsx");
/* harmony import */ var _images_questionblock_png__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../images/questionblock.png */ "./assets/images/questionblock.png");



function Login(props) {
  const {
    username
  } = props;
  const form = react__WEBPACK_IMPORTED_MODULE_0___default.a.useRef();

  const handleSubmit = event => {
    event.preventDefault();
    console.log(form.current.username.value, form.current.password.value);
  };

  const [open, setOpen] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState(false);

  const handleOpen = event => {
    event.preventDefault();
    setOpen(true);
  };

  const handleClose = () => {
    setOpen(false);
  };

  const userLink = username && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", {
    className: "user"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: `~${username}`
  }, username));
  const loginButtonStyle = {
    paddingLeft: 18,
    background: `url(${_images_questionblock_png__WEBPACK_IMPORTED_MODULE_2__["default"]}) no-repeat left center`
  };
  const loginButton = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "/login.php",
    onClick: handleOpen,
    style: loginButtonStyle
  }, "Login");
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "login"
  }, userLink || loginButton, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Modal_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], {
    open: open,
    close: handleClose
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("form", {
    ref: form,
    onSubmit: handleSubmit
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("input", {
    type: "text",
    name: "username",
    placeholder: "Username",
    ref: input => input && input.focus()
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("input", {
    type: "password",
    name: "password",
    placeholder: "Password"
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    type: "submit"
  }, "Login"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    type: "button",
    onClick: handleClose
  }, "Cancel")))));
}

/***/ }),

/***/ "./assets/javascript/Modal.jsx":
/*!*************************************!*\
  !*** ./assets/javascript/Modal.jsx ***!
  \*************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Modal; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react_transition_group__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react-transition-group */ "./node_modules/react-transition-group/esm/index.js");


function Modal(props) {
  const {
    children,
    open = true,
    close = null,
    timeout = 500,
    overlay = true
  } = props;
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react_transition_group__WEBPACK_IMPORTED_MODULE_1__["CSSTransition"], {
    in: open,
    timeout: timeout,
    classNames: "modal",
    unmountOnExit: true
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: "modal modal-container"
  }, overlay && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: "modal-overlay",
    role: "button",
    onClick: close,
    "aria-hidden": "true",
    "aria-label": "close"
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: "modal-content light"
  }, children)));
}

/***/ }),

/***/ "./assets/javascript/NavMenu.jsx":
/*!***************************************!*\
  !*** ./assets/javascript/NavMenu.jsx ***!
  \***************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return NavMenu; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react_icons__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react-icons */ "./node_modules/react-icons/lib/esm/index.js");
/* harmony import */ var react_icons_bi__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! react-icons/bi */ "./node_modules/react-icons/bi/index.esm.js");
/* harmony import */ var _Modal_jsx__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./Modal.jsx */ "./assets/javascript/Modal.jsx");




function NavMenu(props) {
  const [open, setOpen] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState(false);

  const handleOpen = event => {
    event.preventDefault();
    setOpen(!open);
  };

  const handleClose = () => {
    setOpen(false);
  };

  const classname = open ? 'plain active' : 'plain inactive';
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "navmenu"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    type: "button",
    role: "switch",
    "aria-checked": open,
    id: "hamburger",
    className: classname,
    onClick: handleOpen
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react_icons__WEBPACK_IMPORTED_MODULE_1__["IconContext"].Provider, {
    value: {
      size: '30px',
      color: 'white'
    }
  }, open ? /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react_icons_bi__WEBPACK_IMPORTED_MODULE_2__["BiX"], null) : /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react_icons_bi__WEBPACK_IMPORTED_MODULE_2__["BiMenu"], null))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Modal_jsx__WEBPACK_IMPORTED_MODULE_3__["default"], {
    open: open,
    close: handleClose
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("ul", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("li", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "/games"
  }, "Games")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("li", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "/people"
  }, "People")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("li", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "/music"
  }, "Music")))));
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
/* harmony import */ var _images_h1_condensed_png__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../images/h1_condensed.png */ "./assets/images/h1_condensed.png");
/* harmony import */ var _images_twitter_sm_png__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../images/twitter_sm.png */ "./assets/images/twitter_sm.png");
/* harmony import */ var _images_footer_diorama_png__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../images/footer_diorama.png */ "./assets/images/footer_diorama.png");
// Imports





var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(true);
var ___CSS_LOADER_URL_REPLACEMENT_0___ = _node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_1___default()(_images_h1_condensed_png__WEBPACK_IMPORTED_MODULE_2__["default"]);
var ___CSS_LOADER_URL_REPLACEMENT_1___ = _node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_1___default()(_images_twitter_sm_png__WEBPACK_IMPORTED_MODULE_3__["default"]);
var ___CSS_LOADER_URL_REPLACEMENT_2___ = _node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_1___default()(_images_footer_diorama_png__WEBPACK_IMPORTED_MODULE_4__["default"]);
// Module
___CSS_LOADER_EXPORT___.push([module.i, ":root {\n  font: normal 100% sans-serif;\n  font-size: calc(100vw / 25);\n  color: white;\n}\n@media (min-width: 641px) {\n  :root {\n    font-size: calc(100vw / 70);\n  }\n}\n\na,\n.a {\n  color: #3399ff;\n  text-decoration: underline;\n  cursor: pointer;\n}\na:active,\n.a:active {\n  color: #6b3ea8;\n}\na:hover,\n.a:hover {\n  color: #66b3ff;\n  border-color: #66b3ff;\n}\n\nfieldset {\n  margin-left: 0;\n  margin-right: 0;\n  padding: 5px 10px 10px 10px;\n  border: 1px solid #ccc;\n}\n\nlegend {\n  color: #666;\n}\n\ninput[type=text],\ninput[type=password],\ntextarea,\nselect,\n.inputfield {\n  padding: 3px 1px 3px 2px;\n  margin-bottom: 1px;\n  border-width: 1px;\n  border-style: solid;\n  border-color: #666 #bbb #bbb #666;\n  background: white;\n  background: rgba(255, 255, 255, 0.7);\n  border-radius: 2px;\n  outline: none;\n}\n\ntextarea {\n  font-family: monospace;\n}\n\nselect {\n  padding: 2px;\n}\n\noptgroup {\n  padding-top: 2px;\n  font-weight: normal;\n  font-style: italic;\n  color: #777;\n  background-color: #eee;\n}\n\noptgroup > option {\n  padding-left: 20px;\n  background-color: #fff;\n  color: black;\n}\n\noptgroup > option:first-child {\n  margin-top: 2px;\n}\n\nbutton:not(.plain),\ninput[type=button],\ninput[type=submit],\ninput[type=reset],\n.faux-button {\n  padding: 3px 10px;\n  color: #444;\n  text-shadow: 0 -1px #dadada, 0 1px #eee;\n  background: #ddd;\n  background: -moz-linear-gradient(top, #eee 50%, #ddd 50%);\n  background: -webkit-gradient(linear, left top, left bottom, color-stop(50%, #eee), color-stop(50%, #ddd));\n  border-width: 1px;\n  border-style: solid;\n  border-color: #ddd #aaa #aaa #ddd;\n  border-radius: 2px;\n  box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1);\n  cursor: pointer;\n}\nbutton:not(.plain):hover,\ninput[type=button]:hover,\ninput[type=submit]:hover,\ninput[type=reset]:hover,\n.faux-button:hover {\n  border-color: #777;\n}\nbutton:not(.plain):active, button:not(.plain).active,\ninput[type=button]:active,\ninput[type=button].active,\ninput[type=submit]:active,\ninput[type=submit].active,\ninput[type=reset]:active,\ninput[type=reset].active,\n.faux-button:active,\n.faux-button.active {\n  box-shadow: none;\n  background: #ddd;\n  border-color: #aaa #ccc #ccc #aaa;\n}\nbutton:not(.plain)[disabled=disabled],\ninput[type=button][disabled=disabled],\ninput[type=submit][disabled=disabled],\ninput[type=reset][disabled=disabled],\n.faux-button[disabled=disabled] {\n  color: #bbb;\n  cursor: not-allowed;\n}\nbutton:not(.plain).submit:hover,\ninput[type=button].submit:hover,\ninput[type=submit].submit:hover,\ninput[type=reset].submit:hover,\n.faux-button.submit:hover {\n  background: #00a264;\n  border-color: #016c43;\n  color: white;\n  text-shadow: none;\n}\nbutton:not(.plain).cancel:hover,\ninput[type=button].cancel:hover,\ninput[type=submit].cancel:hover,\ninput[type=reset].cancel:hover,\n.faux-button.cancel:hover {\n  background: #dd3333;\n  border-color: #a81c1c;\n  color: white;\n  text-shadow: none;\n}\n\nimg {\n  vertical-align: middle;\n}\n\nh1 {\n  font-weight: 300;\n  line-height: 1.1;\n  margin-top: 0;\n  margin-bottom: 0.75em;\n  color: whitesmoke;\n  text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.2);\n}\n\nh2 {\n  margin-bottom: 0.5em;\n  font-weight: normal;\n  color: whitesmoke;\n}\n\nh3 {\n  margin: 1em 0 0.5em;\n}\n\nh4 {\n  margin: 1em 0 0.5em;\n}\n\nh5 {\n  margin: 1em 0 0.5em;\n  font-size: 1.25rem;\n}\n\nh6 {\n  margin: 1em 0 0.5em;\n}\n\n.grid-page, body > footer, body {\n  display: grid;\n  grid-template-columns: [margin-start] 1rem [narrow-gutter-start] 2.5rem [wide-gutter-start] auto [wide-gutter-end] 2.5rem [narrow-gutter-end] 1rem [margin-end];\n}\n.grid-page > *, body > footer > *, body > * {\n  grid-column: narrow-gutter-start/narrow-gutter-end;\n}\n@media (min-width: 641px) {\n  .grid-page > *, body > footer > *, body > * {\n    grid-column: wide-gutter-start/wide-gutter-end;\n  }\n}\n\n.fullwidth, body > footer, body > header {\n  grid-column: margin-start/margin-end;\n}\n\nbody {\n  min-height: 100vh;\n  box-sizing: border-box;\n  padding: 0;\n  background-color: #18191a;\n  text-align: left;\n  grid-template-rows: min-content auto max-content;\n}\nbody > header, body > main, body > footer {\n  padding-top: 1rem;\n  padding-bottom: 1rem;\n}\n@media (min-width: 641px) {\n  body > header, body > main, body > footer {\n    padding-top: 3.5rem;\n  }\n}\n@media (min-width: 641px) {\n  body > header, body > main, body > footer {\n    padding-bottom: 3.5rem;\n  }\n}\n\nbody > header {\n  background-color: #18191a;\n  border-bottom: 1px solid #242526;\n}\n\n#navmenu {\n  height: 30px;\n}\n#navmenu button {\n  position: fixed;\n  z-index: 9;\n  top: 1rem;\n  left: 1rem;\n  margin: 0;\n  padding: 0;\n  border: none;\n  text-align: left;\n  cursor: pointer;\n  background-color: transparent;\n  display: flex;\n  align-items: center;\n}\n@media (min-width: 641px) {\n  #navmenu button {\n    top: 3.5rem;\n  }\n}\n@media (min-width: 641px) {\n  #navmenu button {\n    left: 3.5rem;\n  }\n}\n#navmenu button:after {\n  content: \"\";\n  width: 163px;\n  height: 18px;\n  margin: 2px 0 0 8px;\n  background: transparent url(" + ___CSS_LOADER_URL_REPLACEMENT_0___ + ") no-repeat scroll 0 0;\n}\n#navmenu button:hover::after {\n  background-position: 0 -19px;\n}\n#navmenu button.active svg {\n  color: black !important;\n}\n@media (min-width: 641px) {\n  #navmenu button {\n    margin-left: -38px;\n  }\n}\n#navmenu .modal,\n#navmenu .modal-overlay {\n  z-index: 7;\n}\n#navmenu .modal-content {\n  z-index: 8;\n}\n\n#login .modal-content {\n  width: 225px;\n  margin-right: auto;\n  margin-left: auto;\n  background-color: transparent !important;\n}\n#login form {\n  margin-top: -1em;\n  display: flex;\n  flex-direction: column;\n  align-items: flex-start;\n}\n#login form > * {\n  margin-top: 1em;\n}\n#login form input {\n  width: 100%;\n  padding: 6px 0 6px 8px;\n  font-size: 14px;\n  color: #666;\n  background: white;\n  background: -moz-linear-gradient(top, #e0e0e0, white 7px);\n  background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #e0e0e0), color-stop(100%, white));\n  border-width: 0;\n  border-radius: 3px;\n}\n#login form button {\n  padding: 5px 15px;\n  font-size: 15px;\n  font-weight: bold;\n  border-width: 0;\n  box-shadow: 1px 1px 3px 1px black;\n  -moz-box-shadow: 1px 1px 3px 1px black;\n  -webkit-box-shadow: 1px 1px 3px 1px black;\n}\n#login form button:active {\n  margin: 1px 0 0 1px;\n  box-shadow: 0 0 3px 1px black;\n  -moz-box-shadow: 0 0 3px 1px black;\n  -webkit-box-shadow: 0 0 3px 1px black;\n}\n#login form button + button {\n  margin-left: 1em;\n}\n\nbody > #content {\n  padding-top: 1rem;\n  padding-bottom: 1rem;\n}\n@media (min-width: 641px) {\n  body > #content {\n    padding-top: 3.5rem;\n  }\n}\n@media (min-width: 641px) {\n  body > #content {\n    padding-bottom: 3.5rem;\n  }\n}\n\nbody > footer {\n  position: relative;\n  z-index: 1;\n  padding-top: 1rem;\n  padding-bottom: 1rem;\n  border-top: 1px solid #242526;\n  color: #999;\n}\n@media (min-width: 641px) {\n  body > footer {\n    padding-top: 3.5rem;\n  }\n}\n@media (min-width: 641px) {\n  body > footer {\n    padding-bottom: 3.5rem;\n  }\n}\nbody > footer a {\n  color: #aaa;\n}\nbody > footer a:hover {\n  color: #ccc;\n}\nbody > footer h5 {\n  margin: 0 0 5px;\n  font-size: 15px;\n}\nbody > footer ul {\n  margin: 0;\n  padding: 0;\n  list-style: none;\n}\nbody > footer ul li {\n  margin: 0;\n  padding: 0;\n}\nbody > footer .about,\nbody > footer .featured {\n  display: none;\n}\nbody > footer .about li a,\nbody > footer .featured li a {\n  display: block;\n  margin: 0 14px 7px 0;\n  font-size: 14px;\n  background-position: left center;\n  background-repeat: no-repeat;\n}\nbody > footer .about li a .link-twitter,\nbody > footer .featured li a .link-twitter {\n  padding-left: 20px;\n  background: url(" + ___CSS_LOADER_URL_REPLACEMENT_1___ + ") no-repeat left center;\n}\nbody > footer #diorama {\n  width: 100%;\n  height: 20px;\n}\nbody > footer #diorama div {\n  position: absolute;\n  background-image: url(" + ___CSS_LOADER_URL_REPLACEMENT_2___ + ");\n}\n\n.dark, #login .modal-content {\n  background-color: black;\n  color: white;\n}\n.dark a, #login .modal-content a {\n  color: white;\n}\n.dark a:hover, #login .modal-content a:hover {\n  color: lightgray;\n}\n\n.light {\n  background-color: white;\n  color: black;\n}\n.light a {\n  color: black;\n}\n.light a:hover {\n  color: #2e2e2e;\n}\n\n.red {\n  color: #dd3333;\n}\n.red:hover {\n  color: #f17878;\n}\n\n.modal {\n  position: fixed;\n  z-index: 10;\n  top: 0;\n  right: 0;\n  bottom: 0;\n  left: 0;\n  display: grid;\n}\n\n.modal-container.modal-enter {\n  opacity: 0;\n}\n.modal-container.modal-enter-active {\n  opacity: 1;\n  transition: all 500ms;\n}\n.modal-container.modal-exit {\n  opacity: 1;\n}\n.modal-container.modal-exit-active {\n  opacity: 0;\n  transition: opacity 500ms;\n}\n\n.modal-overlay {\n  width: 100%;\n  height: 100%;\n  z-index: 10;\n  /* places the modal overlay between the main page and the modal content*/\n  background-color: rgba(0, 0, 0, 0.95);\n  position: fixed;\n  top: 0;\n  left: 0;\n  margin: 0;\n  padding: 0;\n  transition: all 0.3s;\n}\n\n.modal-content {\n  width: auto;\n  margin: 0.5rem;\n  padding: 2rem;\n  position: relative;\n  z-index: 11;\n  /* places the modal dialog on top of overlay */\n  top: 0;\n  left: 0;\n  display: flex;\n  flex-direction: column;\n  background-color: white;\n}\n.modal-content h5 {\n  margin-top: 0;\n}\n@media (min-width: 640px) {\n  .modal-content {\n    margin: auto;\n    max-width: 500px;\n  }\n}\n\n.modal-close {\n  color: #aaa;\n  line-height: 50px;\n  font-size: 80%;\n  position: absolute;\n  right: 0;\n  text-align: center;\n  top: 0;\n  width: 70px;\n  text-decoration: none;\n}\n.modal-close:hover {\n  color: black;\n}\n\n.user {\n  height: 20px;\n  line-height: 20px;\n}\n.user .user-username {\n  display: inline-block;\n  vertical-align: middle;\n}\n.user .user-avatar.thumbnail {\n  display: inline-block;\n  vertical-align: middle;\n  margin-right: 5px;\n}\n\n.user-avatar.big img {\n  width: 144px;\n  height: 144px;\n}\n.user-avatar.icon img {\n  width: 48px;\n  height: 48px;\n}\n.user-avatar.thumbnail img {\n  width: 20px;\n  height: 20px;\n}", "",{"version":3,"sources":["webpack://app.scss"],"names":[],"mappings":"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","sourcesContent":["$blue: #3399ff;\r\n$lightblue: #66b3ff;\r\n$darkblue: #336699;\r\n$red: #dd3333;\r\n$green: #00a264;\r\n$purple: #6b3ea8;\r\n$color-body: rgb(24, 25, 26);\r\n$color-surface: rgb(36, 37, 38);\r\n$color-comment: rgb(58, 59, 60);\r\n\r\n$img-dir: \"../images\";\r\n\r\n// Breakpoint settings and methods\r\n\r\n$breakpoint-mobile: 640px;\r\n\r\n@mixin breakpoint($view) {\r\n    @if $view == mobile {\r\n        @media (max-width: $breakpoint-mobile) { @content; }\r\n    }\r\n    @else {\r\n        @media (min-width: $breakpoint-mobile + 1px) { @content; }\r\n    }\r\n}\r\n\r\n// Spacing settings and methods\r\n\r\n$spacing-mobile: 1rem;\r\n$spacing-monitor: 3.5rem;\r\n\r\n@mixin spacing($prop) {\r\n    #{$prop}: $spacing-mobile;\r\n    @include breakpoint(screen) {\r\n        #{$prop}: $spacing-monitor;\r\n    }\r\n}\r\n\r\n// Top Level (Global)\r\n\r\n:root {\r\n    font: normal 100% sans-serif;\r\n    font-size: calc(100vw / 25);\r\n    color: white;\r\n    @include breakpoint(screen) {\r\n        font-size: calc(100vw / 70);\r\n    }\r\n}\r\n\r\na,\r\n.a {\r\n    color: $blue;\r\n    text-decoration: underline;\r\n    cursor: pointer;\r\n    &:active {\r\n        color: $purple;\r\n    }\r\n    &:hover {\r\n        color: $lightblue;\r\n        border-color: $lightblue;\r\n    }\r\n}\r\n\r\nfieldset {\r\n    margin-left: 0;\r\n    margin-right: 0;\r\n    padding: 5px 10px 10px 10px;\r\n    border: 1px solid #ccc;\r\n}\r\nlegend {\r\n    color: #666;\r\n}\r\ninput[type=\"text\"],\r\ninput[type=\"password\"],\r\ntextarea,\r\nselect,\r\n.inputfield {\r\n    padding: 3px 1px 3px 2px;\r\n    margin-bottom: 1px;\r\n    border-width: 1px;\r\n    border-style: solid;\r\n    border-color: #666 #bbb #bbb #666;\r\n    background: white;\r\n    background: rgba(255, 255, 255, 0.7);\r\n    border-radius: 2px;\r\n    outline: none;\r\n}\r\ntextarea {\r\n    font-family: monospace;\r\n}\r\nselect {\r\n    padding: 2px;\r\n}\r\noptgroup {\r\n    padding-top: 2px;\r\n    font-weight: normal;\r\n    font-style: italic;\r\n    color: #777;\r\n    background-color: #eee;\r\n}\r\noptgroup > option {\r\n    padding-left: 20px;\r\n    background-color: #fff;\r\n    color: black;\r\n}\r\noptgroup > option:first-child {\r\n    margin-top: 2px;\r\n}\r\n\r\n// .button-blue {\r\n// \t@link-color: darken(@lightblue, 16%);\r\n// \tcolor:rgba(255,255,255,.93); text-shadow:0 -1px @link-color, 0 1px @lightblue;\r\n// \tbackground:@link-color; background:-moz-linear-gradient(top, @lightblue 50%, @link-color 50%); background:-webkit-gradient(linear, left top, left bottom, color-stop(50%,@lightblue), color-stop(50%,@link-color));\r\n// \tborder-color:darken(@lightblue, 10%) darken(@link-color, 5%) darken(@link-color, 5%) darken(@lightblue, 5%);\r\n// \tbox-shadow:0 1px 1px rgba(0,0,0,.2);\r\n// \t&:hover { border-color:darken(@link-color, 25%); }\r\n// \t&:active { box-shadow:none; background:darken(@lightblue, 5%); border-color:darken(@link-color, 12%) darken(@lightblue, 12%) darken(@lightblue, 12%) darken(@link-color, 12%); }\r\n// }\r\nbutton:not(.plain),\r\ninput[type=\"button\"],\r\ninput[type=\"submit\"],\r\ninput[type=\"reset\"],\r\n.faux-button {\r\n    padding: 3px 10px;\r\n    color: #444;\r\n    text-shadow: 0 -1px #dadada, 0 1px #eee;\r\n    background: #ddd;\r\n    background: -moz-linear-gradient(top, #eee 50%, #ddd 50%);\r\n    background: -webkit-gradient(linear, left top, left bottom, color-stop(50%, #eee), color-stop(50%, #ddd));\r\n    border-width: 1px;\r\n    border-style: solid;\r\n    border-color: #ddd #aaa #aaa #ddd;\r\n    border-radius: 2px;\r\n    box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1);\r\n    cursor: pointer;\r\n    &:hover {\r\n        border-color: #777;\r\n    }\r\n    &:active,\r\n    &.active {\r\n        box-shadow: none;\r\n        background: #ddd;\r\n        border-color: #aaa #ccc #ccc #aaa;\r\n    }\r\n    &[disabled=\"disabled\"] {\r\n        color: #bbb;\r\n        cursor: not-allowed;\r\n    }\r\n    &.submit:hover {\r\n        background: $green;\r\n        border-color: #016c43;\r\n        color: white;\r\n        text-shadow: none;\r\n    }\r\n    &.cancel:hover {\r\n        background: $red;\r\n        border-color: darken($red, 15%);\r\n        color: white;\r\n        text-shadow: none;\r\n    }\r\n}\r\n\r\nimg {\r\n    vertical-align: middle;\r\n}\r\n\r\nh1 {\r\n    font-weight: 300;\r\n    line-height: 1.1;\r\n    margin-top: 0;\r\n    margin-bottom: 0.75em;\r\n    color: whitesmoke;\r\n    text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.2);\r\n}\r\nh2 {\r\n    margin-bottom: 0.5em;\r\n    font-weight: normal;\r\n    color: whitesmoke;\r\n}\r\nh3 {\r\n    margin: 1em 0 0.5em;\r\n}\r\nh4 {\r\n    margin: 1em 0 0.5em;\r\n}\r\nh5 {\r\n    margin: 1em 0 0.5em;\r\n    font-size: 1.25rem;\r\n}\r\nh6 {\r\n    margin: 1em 0 0.5em;\r\n}\r\n\r\n// Page Layouts\r\n\r\n// A template for fullwidth elements;\r\n// Top-level <body> and other full-width elements.\r\n.grid-page {\r\n    display: grid;\r\n    grid-template-columns: [margin-start] $spacing-mobile [narrow-gutter-start] $spacing-monitor - $spacing-mobile [wide-gutter-start] auto [wide-gutter-end] $spacing-monitor - $spacing-mobile [narrow-gutter-end] $spacing-mobile [margin-end];\r\n    & > * {\r\n        grid-column: narrow-gutter-start / narrow-gutter-end;\r\n        @include breakpoint(screen) {\r\n            grid-column: wide-gutter-start / wide-gutter-end;\r\n        }\r\n    }\r\n}\r\n.fullwidth {\r\n    grid-column: margin-start / margin-end;\r\n}\r\n\r\nbody {\r\n    min-height: 100vh;\r\n    box-sizing: border-box;\r\n    padding: 0;\r\n    background: {\r\n        color: $color-body;\r\n    }\r\n    text-align: left;\r\n    @extend .grid-page;\r\n    grid-template-rows: min-content auto max-content;\r\n    > header, > main, > footer {\r\n        @include spacing(padding-top);\r\n        @include spacing(padding-bottom);\r\n    }\r\n}\r\n\r\nbody > header {\r\n    @extend .fullwidth;\r\n    background-color: $color-body;\r\n    border-bottom: 1px solid $color-surface;\r\n}\r\n#navmenu {\r\n    height: 30px;\r\n    button {\r\n        position: fixed;\r\n        z-index: 9; // Behind modal overlay\r\n        @include spacing(\"top\");\r\n        @include spacing(\"left\");\r\n        margin: 0;\r\n        padding: 0;\r\n        border: none;\r\n        text-align: left;\r\n        cursor: pointer;\r\n        background-color: transparent;\r\n        display: flex;\r\n        align-items: center;\r\n        &:after {\r\n            content: \"\";\r\n            width: 163px;\r\n            height: 18px;\r\n            margin: 2px 0 0 8px;\r\n            background: transparent url(\"#{$img-dir}/h1_condensed.png\") no-repeat scroll 0 0;\r\n        }\r\n        &:hover::after {\r\n            background-position: 0 -19px;\r\n        }\r\n        &.active svg {\r\n            color: black !important;\r\n        }\r\n        @include breakpoint(screen) {\r\n            margin-left: -38px; // hamburger:30px; bgimg padding:8px;\r\n        }\r\n    }\r\n    .modal,\r\n    .modal-overlay {\r\n        z-index: 7;\r\n    }\r\n    .modal-content {\r\n        z-index: 8;\r\n    }\r\n}\r\n#login {\r\n    .modal-content {\r\n        width: 225px;\r\n        margin-right: auto;\r\n        margin-left: auto;\r\n        @extend .dark;\r\n        background-color: transparent !important;\r\n    }\r\n    form {\r\n        margin-top: -1em;\r\n        display: flex;\r\n        flex-direction: column;\r\n        align-items: flex-start;\r\n        & > * {\r\n            margin-top: 1em;\r\n        }\r\n        input {\r\n            width: 100%;\r\n            padding: 6px 0 6px 8px;\r\n            font-size: 14px;\r\n            color: #666;\r\n            background: white;\r\n            background: -moz-linear-gradient(top, #e0e0e0, white 7px);\r\n            background: -webkit-gradient(\r\n                linear,\r\n                left top,\r\n                left bottom,\r\n                color-stop(0%, #e0e0e0),\r\n                color-stop(100%, white)\r\n            );\r\n            border-width: 0;\r\n            border-radius: 3px;\r\n        }\r\n        button {\r\n            padding: 5px 15px;\r\n            font-size: 15px;\r\n            font-weight: bold;\r\n            border-width: 0;\r\n            box-shadow: 1px 1px 3px 1px black;\r\n            -moz-box-shadow: 1px 1px 3px 1px black;\r\n            -webkit-box-shadow: 1px 1px 3px 1px black;\r\n            &:active {\r\n                margin: 1px 0 0 1px;\r\n                box-shadow: 0 0 3px 1px black;\r\n                -moz-box-shadow: 0 0 3px 1px black;\r\n                -webkit-box-shadow: 0 0 3px 1px black;\r\n            }\r\n            & + button {\r\n                margin-left: 1em;\r\n            }\r\n        }\r\n    }\r\n}\r\n\r\nbody > #content {\r\n    @include spacing(padding-top);\r\n    @include spacing(padding-bottom);\r\n}\r\n\r\nbody > footer {\r\n    @extend .fullwidth;\r\n    @extend .grid-page;\r\n    position: relative;\r\n    z-index: 1;\r\n    @include spacing(padding-top);\r\n    @include spacing(padding-bottom);\r\n    border-top: 1px solid $color-surface;\r\n    color: #999;\r\n    a {\r\n        color: #aaa;\r\n    }\r\n    a:hover {\r\n        color: #ccc;\r\n    }\r\n    h5 {\r\n        margin: 0 0 5px;\r\n        font-size: 15px;\r\n    }\r\n    ul {\r\n        margin: 0;\r\n        padding: 0;\r\n        list-style: none;\r\n        li {\r\n            margin: 0;\r\n            padding: 0;\r\n        }\r\n    }\r\n    .about,\r\n    .featured {\r\n        display: none;\r\n        li {\r\n            a {\r\n                display: block;\r\n                margin: 0 14px 7px 0;\r\n                font-size: 14px;\r\n                background-position: left center;\r\n                background-repeat: no-repeat;\r\n                .link-twitter {\r\n                    padding-left: 20px;\r\n                    background: url(\"#{$img-dir}/twitter_sm.png\") no-repeat left center;\r\n                }\r\n            }\r\n        }\r\n    }\r\n    .featured ul li {\r\n    }\r\n    #diorama {\r\n        width: 100%;\r\n        height: 20px; // Functions as padding\r\n        div {\r\n            position: absolute;\r\n            background-image: url(\"#{$img-dir}/footer_diorama.png\");\r\n        }\r\n    }\r\n}\r\n\r\n// Custom Classes\r\n\r\n.dark {\r\n    background-color: black;\r\n    color: white;\r\n    a {\r\n        color: white;\r\n        &:hover {\r\n            color: lightgray;\r\n        }\r\n    }\r\n}\r\n.light {\r\n    background-color: white;\r\n    color: black;\r\n    a {\r\n        color: black;\r\n        &:hover {\r\n            color: rgb(46, 46, 46);\r\n        }\r\n    }\r\n}\r\n.red {\r\n    color: $red;\r\n    &:hover {\r\n        color: #f17878;\r\n    }\r\n}\r\n\r\n.modal {\r\n    position: fixed;\r\n    z-index: 10;\r\n    top: 0;\r\n    right: 0;\r\n    bottom: 0;\r\n    left: 0;\r\n    display: grid;\r\n}\r\n.modal-container {\r\n    &.modal-enter {\r\n        opacity: 0;\r\n    }\r\n    &.modal-enter-active {\r\n        opacity: 1;\r\n        transition: all 500ms;\r\n    }\r\n    &.modal-exit {\r\n        opacity: 1;\r\n    }\r\n    &.modal-exit-active {\r\n        opacity: 0;\r\n        transition: opacity 500ms;\r\n    }\r\n}\r\n.modal-overlay {\r\n    width: 100%;\r\n    height: 100%;\r\n    z-index: 10; /* places the modal overlay between the main page and the modal content*/\r\n    background-color: rgba(0, 0, 0, 0.95);\r\n    position: fixed;\r\n    top: 0;\r\n    left: 0;\r\n    margin: 0;\r\n    padding: 0;\r\n    transition: all 0.3s;\r\n}\r\n.modal-content {\r\n    width: auto;\r\n    margin: 0.5rem;\r\n    padding: 2rem;\r\n    position: relative;\r\n    z-index: 11; /* places the modal dialog on top of overlay */\r\n    top: 0;\r\n    left: 0;\r\n    display: flex;\r\n    flex-direction: column;\r\n    background-color: white;\r\n    h5 {\r\n        margin-top: 0;\r\n    }\r\n    @media (min-width: $breakpoint-mobile) {\r\n        margin: auto;\r\n        max-width: 500px;\r\n    }\r\n}\r\n.modal-close {\r\n    color: #aaa;\r\n    line-height: 50px;\r\n    font-size: 80%;\r\n    position: absolute;\r\n    right: 0;\r\n    text-align: center;\r\n    top: 0;\r\n    width: 70px;\r\n    text-decoration: none;\r\n    &:hover {\r\n        color: black;\r\n    }\r\n}\r\n\r\n.user {\r\n    height: 20px;\r\n    line-height: 20px;\r\n    .user-username {\r\n        display: inline-block;\r\n        vertical-align: middle;\r\n    }\r\n    .user-avatar.thumbnail {\r\n        display: inline-block;\r\n        vertical-align: middle;\r\n        margin-right: 5px;\r\n    }\r\n}\r\n.user-avatar {\r\n    &.big img {\r\n        width: 144px;\r\n        height: 144px;\r\n    }\r\n    &.icon img {\r\n        width: 48px;\r\n        height: 48px;\r\n    }\r\n    &.thumbnail img {\r\n        width: 20px;\r\n        height: 20px;\r\n    }\r\n}\r\n"],"sourceRoot":""}]);
// Exports
/* harmony default export */ __webpack_exports__["default"] = (___CSS_LOADER_EXPORT___);


/***/ })

/******/ });
//# sourceMappingURL=app_bundle.js.map