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
/******/ 		"test": 0
/******/ 	};
/******/
/******/ 	var deferredModules = [];
/******/
/******/ 	// script path function
/******/ 	function jsonpScriptSrc(chunkId) {
/******/ 		return __webpack_require__.p + "" + ({"print":"print"}[chunkId]||chunkId) + ".bundle.js"
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
/******/ 	deferredModules.push(["./browser/src/test.js","vendor"]);
/******/ 	// run deferred modules when ready
/******/ 	return checkDeferredModules();
/******/ })
/************************************************************************/
/******/ ({

/***/ "./browser/src/components/Test.jsx":
/*!*****************************************!*\
  !*** ./browser/src/components/Test.jsx ***!
  \*****************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);

const themes = {
  light: {
    foreground: "#000000",
    background: "#eeeeee",
    name: 'light'
  },
  dark: {
    foreground: "#ffffff",
    background: "#222222",
    name: 'dark'
  }
}; // Create a context for the current theme (with "light" as the default).

const ThemeContext = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createContext(themes.light);

function Test() {
  // Use a Provider to pass the current theme to the tree below.
  // Any component can read it, no matter how deep it is.
  // In this example, we're passing "dark" as the current value.
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(ThemeContext.Provider, {
    value: themes.dark
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(Toolbar, null));
} // A component in the middle doesn't have to
// pass the theme down explicitly anymore.


function Toolbar() {
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(ThemedButton, null));
}

function ThemedButton() {
  // Assign a contextType to read the current theme context.
  // React will find the closest theme Provider above and use its value.
  // In this example, the current theme is "dark".
  const theme = react__WEBPACK_IMPORTED_MODULE_0___default.a.useContext(ThemeContext);
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    style: {
      background: theme.background,
      color: theme.foreground
    }
  }, theme.name);
}

/* harmony default export */ __webpack_exports__["default"] = (Test);

/***/ }),

/***/ "./browser/src/test.js":
/*!*****************************!*\
  !*** ./browser/src/test.js ***!
  \*****************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react_dom__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react-dom */ "./node_modules/react-dom/index.js");
/* harmony import */ var react_dom__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(react_dom__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _components_Test_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./components/Test.jsx */ "./browser/src/components/Test.jsx");
/* harmony import */ var _styles_test_css__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../styles/test.css */ "./browser/styles/test.css");

 // Components to render




const Content = () => {
  const [open, setOpen] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState(true);
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("h1", null, "Test Testing Testy"), open && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", {
    className: "foo"
  }, "Lorem ipsum"), " dolor sit amet ", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "#consectetur"
  }, "consectetur adipisicing elit"), ". Eveniet voluptas incidunt atque ipsam, nobis quis inventore, velit libero vel autem tempora, fugit soluta excepturi ", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "#foo"
  }, "voluptatum"), "! Soluta possimus nihil dolore hic."), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Lorem ipsum dolor sit amet consectetur adipisicing elit. Aperiam, repellendus ullam cumque sequi deserunt cum possimus, deleniti impedit pariatur atque eligendi. Eius debitis delectus maxime esse a, odio sint mollitia!"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "#bar"
  }, "Lorem ipsum dolor sit amet consectetur"), ", adipisicing elit. Quis tenetur facilis ipsum doloremque magni cum. Praesentium reiciendis vitae omnis ex sint eaque eos necessitatibus assumenda atque reprehenderit, commodi quod. Nam! Lorem ipsum dolor sit amet consectetur adipisicing elit. Obcaecati consectetur similique nulla veritatis a impedit provident eaque dignissimos facere soluta voluptate ab aliquam quidem culpa dolores hic excepturi, eius quae?"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Lorem ipsum dolor sit amet consectetur adipisicing elit. Alias facere magni culpa molestiae voluptates ducimus? Ducimus minus nesciunt tempora ad asperiores! Totam autem dolore eos delectus reprehenderit ipsa animi omnis."), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Lorem ipsum dolor sit, amet consectetur adipisicing elit. ", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "#baz"
  }, "Itaque beatae"), " eaque praesentium modi voluptates libero obcaecati earum? Officia impedit distinctio deleniti exercitationem delectus! Assumenda, hic a eaque nobis velit quis."), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Lorem ipsum dolor sit, amet consectetur adipisicing elit. Accusamus magni ut aliquam officiis nostrum consequatur tempore, at repudiandae, laudantium exercitationem itaque cum, et voluptate suscipit modi unde ad doloremque sit! Lorem ipsum dolor sit amet consectetur adipisicing elit. Autem maiores quisquam distinctio quos qui adipisci voluptates perferendis officia commodi, fugit eius est ut corrupti reprehenderit fuga quibusdam, cum itaque sequi?"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Lorem ipsum, dolor sit amet consectetur adipisicing elit. Aperiam deserunt ea natus iusto ipsa, labore in consectetur, beatae commodi voluptas hic, ratione asperiores dicta accusantium optio quas unde omnis error!"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Lorem ipsum dolor sit amet consectetur adipisicing elit. Ipsa maxime quod ex iure eius et, sint doloremque! Libero exercitationem pariatur hic dignissimos, dolorum consequuntur odio consectetur voluptate accusamus voluptatem a."), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Fin."), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("h1", null, ">START GAME Options Foo___foo.bar", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("br", null), "____________ (Layout font) Pixel Emulator"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("h1", {
    style: {
      fontFamily: 'Press Start'
    }
  }, ">START GAME Options Foo___foo.bar", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("br", null), "____________ (Monospace font) Press Start"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("h1", {
    style: {
      fontFamily: 'Press Start 2P'
    }
  }, ">START GAME Options Foo___foo.bar", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("br", null), "____________ Press Start 2P"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("h1", {
    style: {
      fontFamily: 'Emulogic'
    }
  }, ">START GAME Options Foo___foo.bar", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("br", null), "____________ Emulogic"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("h1", {
    style: {
      fontFamily: 'Yoster Island'
    }
  }, ">START GAME Options Foo___foo.bar", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("br", null), "____________"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("h1", {
    style: {
      fontFamily: 'Bc.BMP07_A'
    }
  }, ">START GAME Options Foo___foo.bar", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("br", null), "____________"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("h1", {
    style: {
      fontFamily: 'Bc.BMP07_K'
    }
  }, ">START GAME Options Foo___foo.bar", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("br", null), "____________"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("h1", {
    style: {
      fontFamily: 'NineteenNinetySeven'
    }
  }, ">START GAME Options Foo___foo.bar", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("br", null), "____________"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("h1", {
    style: {
      fontFamily: 'Barcade Brawl'
    }
  }, ">START GAME Options Foo___foo.bar", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("br", null), "____________"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("h1", {
    style: {
      fontFamily: 'Barcade Brawl'
    }
  }, ">START GAME Options Foo___foo.bar", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("br", null), "____________"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("h1", {
    style: {
      fontFamily: 'Super Legend Boy'
    }
  }, ">START GAME Options Foo___foo.bar", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("br", null), "____________")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    type: "button",
    onClick: () => setOpen(!open)
  }, "Toggle filler text"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Env"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("ul", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("li", null, "ENVIRONMENT: ", "development"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("li", null, "HOST_DOMAIN: ", "http://vgsite")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("h2", null, "Testing"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_components_Test_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], null));
};

react_dom__WEBPACK_IMPORTED_MODULE_1___default.a.render( /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(Content), document.getElementById('content')); // Demonstrates lazy loading files

function printComponent() {
  const element = document.createElement('div');
  const button = document.createElement('button');
  const br = document.createElement('br');
  button.innerHTML = 'Click me and look at the console! But not before I lazy load a js component...';
  element.appendChild(br);
  element.appendChild(button); // Note that because a network request is involved, some indication
  // of loading would need to be shown in a production-level site/app.

  button.onclick = e => Promise.all(/*! import() | print */[__webpack_require__.e("vendor"), __webpack_require__.e("print")]).then(__webpack_require__.bind(null, /*! ./lib/print */ "./browser/src/lib/print.js")).then(module => {
    // Note that when using import() on ES6 modules you must reference the .default property as it's the actual module object that will be returned when the promise is resolved.
    const print = module.default;
    print();
  });

  return element;
}

document.getElementById('content').appendChild(printComponent()); // Router
// const element = (
//     <>
//         <Router>
//             <Page />
//         </Router>
//     </>
// );
// ReactDOM.render(element, document.getElementById('root'));

/***/ }),

/***/ "./browser/styles/test.css":
/*!*********************************!*\
  !*** ./browser/styles/test.css ***!
  \*********************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

/******/ });
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vd2VicGFjay9ib290c3RyYXAiLCJ3ZWJwYWNrOi8vLy4vYnJvd3Nlci9zcmMvY29tcG9uZW50cy9UZXN0LmpzeCIsIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy90ZXN0LmpzIiwid2VicGFjazovLy8uL2Jyb3dzZXIvc3R5bGVzL3Rlc3QuY3NzIl0sIm5hbWVzIjpbInRoZW1lcyIsImxpZ2h0IiwiZm9yZWdyb3VuZCIsImJhY2tncm91bmQiLCJuYW1lIiwiZGFyayIsIlRoZW1lQ29udGV4dCIsIlJlYWN0IiwiY3JlYXRlQ29udGV4dCIsIlRlc3QiLCJUb29sYmFyIiwiVGhlbWVkQnV0dG9uIiwidGhlbWUiLCJ1c2VDb250ZXh0IiwiY29sb3IiLCJDb250ZW50Iiwib3BlbiIsInNldE9wZW4iLCJ1c2VTdGF0ZSIsImZvbnRGYW1pbHkiLCJwcm9jZXNzIiwiUmVhY3RET00iLCJyZW5kZXIiLCJjcmVhdGVFbGVtZW50IiwiZG9jdW1lbnQiLCJnZXRFbGVtZW50QnlJZCIsInByaW50Q29tcG9uZW50IiwiZWxlbWVudCIsImJ1dHRvbiIsImJyIiwiaW5uZXJIVE1MIiwiYXBwZW5kQ2hpbGQiLCJvbmNsaWNrIiwiZSIsInRoZW4iLCJtb2R1bGUiLCJwcmludCIsImRlZmF1bHQiXSwibWFwcGluZ3MiOiI7UUFBQTtRQUNBO1FBQ0E7UUFDQTtRQUNBOztRQUVBO1FBQ0E7UUFDQTtRQUNBLFFBQVEsb0JBQW9CO1FBQzVCO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTs7UUFFQTtRQUNBO1FBQ0E7O1FBRUE7UUFDQTs7UUFFQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0EsaUJBQWlCLDRCQUE0QjtRQUM3QztRQUNBO1FBQ0Esa0JBQWtCLDJCQUEyQjtRQUM3QztRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBOztRQUVBO1FBQ0E7O1FBRUE7UUFDQTs7UUFFQTtRQUNBO1FBQ0E7UUFD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QyxLQUFLLEdBQUdELE1BQU0sQ0FBQ0UsT0FBckI7QUFFQUQsU0FBSztBQUNSLEdBTHFCLENBQXRCOztBQU9BLFNBQU9ULE9BQVA7QUFDSDs7QUFFREgsUUFBUSxDQUFDQyxjQUFULENBQXdCLFNBQXhCLEVBQW1DTSxXQUFuQyxDQUErQ0wsY0FBYyxFQUE3RCxFLENBRUE7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUVBLDZEOzs7Ozs7Ozs7Ozs7QUN2RkE7QUFBQSIsImZpbGUiOiJ0ZXN0LmJ1bmRsZS5qcyIsInNvdXJjZXNDb250ZW50IjpbIiBcdC8vIGluc3RhbGwgYSBKU09OUCBjYWxsYmFjayBmb3IgY2h1bmsgbG9hZGluZ1xuIFx0ZnVuY3Rpb24gd2VicGFja0pzb25wQ2FsbGJhY2soZGF0YSkge1xuIFx0XHR2YXIgY2h1bmtJZHMgPSBkYXRhWzBdO1xuIFx0XHR2YXIgbW9yZU1vZHVsZXMgPSBkYXRhWzFdO1xuIFx0XHR2YXIgZXhlY3V0ZU1vZHVsZXMgPSBkYXRhWzJdO1xuXG4gXHRcdC8vIGFkZCBcIm1vcmVNb2R1bGVzXCIgdG8gdGhlIG1vZHVsZXMgb2JqZWN0LFxuIFx0XHQvLyB0aGVuIGZsYWcgYWxsIFwiY2h1bmtJZHNcIiBhcyBsb2FkZWQgYW5kIGZpcmUgY2FsbGJhY2tcbiBcdFx0dmFyIG1vZHVsZUlkLCBjaHVua0lkLCBpID0gMCwgcmVzb2x2ZXMgPSBbXTtcbiBcdFx0Zm9yKDtpIDwgY2h1bmtJZHMubGVuZ3RoOyBpKyspIHtcbiBcdFx0XHRjaHVua0lkID0gY2h1bmtJZHNbaV07XG4gXHRcdFx0aWYoT2JqZWN0LnByb3RvdHlwZS5oYXNPd25Qcm9wZXJ0eS5jYWxsKGluc3RhbGxlZENodW5rcywgY2h1bmtJZCkgJiYgaW5zdGFsbGVkQ2h1bmtzW2NodW5rSWRdKSB7XG4gXHRcdFx0XHRyZXNvbHZlcy5wdXNoKGluc3RhbGxlZENodW5rc1tjaHVua0lkXVswXSk7XG4gXHRcdFx0fVxuIFx0XHRcdGluc3RhbGxlZENodW5rc1tjaHVua0lkXSA9IDA7XG4gXHRcdH1cbiBcdFx0Zm9yKG1vZHVsZUlkIGluIG1vcmVNb2R1bGVzKSB7XG4gXHRcdFx0aWYoT2JqZWN0LnByb3RvdHlwZS5oYXNPd25Qcm9wZXJ0eS5jYWxsKG1vcmVNb2R1bGVzLCBtb2R1bGVJZCkpIHtcbiBcdFx0XHRcdG1vZHVsZXNbbW9kdWxlSWRdID0gbW9yZU1vZHVsZXNbbW9kdWxlSWRdO1xuIFx0XHRcdH1cbiBcdFx0fVxuIFx0XHRpZihwYXJlbnRKc29ucEZ1bmN0aW9uKSBwYXJlbnRKc29ucEZ1bmN0aW9uKGRhdGEpO1xuXG4gXHRcdHdoaWxlKHJlc29sdmVzLmxlbmd0aCkge1xuIFx0XHRcdHJlc29sdmVzLnNoaWZ0KCkoKTtcbiBcdFx0fVxuXG4gXHRcdC8vIGFkZCBlbnRyeSBtb2R1bGVzIGZyb20gbG9hZGVkIGNodW5rIHRvIGRlZmVycmVkIGxpc3RcbiBcdFx0ZGVmZXJyZWRNb2R1bGVzLnB1c2guYXBwbHkoZGVmZXJyZWRNb2R1bGVzLCBleGVjdXRlTW9kdWxlcyB8fCBbXSk7XG5cbiBcdFx0Ly8gcnVuIGRlZmVycmVkIG1vZHVsZXMgd2hlbiBhbGwgY2h1bmtzIHJlYWR5XG4gXHRcdHJldHVybiBjaGVja0RlZmVycmVkTW9kdWxlcygpO1xuIFx0fTtcbiBcdGZ1bmN0aW9uIGNoZWNrRGVmZXJyZWRNb2R1bGVzKCkge1xuIFx0XHR2YXIgcmVzdWx0O1xuIFx0XHRmb3IodmFyIGkgPSAwOyBpIDwgZGVmZXJyZWRNb2R1bGVzLmxlbmd0aDsgaSsrKSB7XG4gXHRcdFx0dmFyIGRlZmVycmVkTW9kdWxlID0gZGVmZXJyZWRNb2R1bGVzW2ldO1xuIFx0XHRcdHZhciBmdWxmaWxsZWQgPSB0cnVlO1xuIFx0XHRcdGZvcih2YXIgaiA9IDE7IGogPCBkZWZlcnJlZE1vZHVsZS5sZW5ndGg7IGorKykge1xuIFx0XHRcdFx0dmFyIGRlcElkID0gZGVmZXJyZWRNb2R1bGVbal07XG4gXHRcdFx0XHRpZihpbnN0YWxsZWRDaHVua3NbZGVwSWRdICE9PSAwKSBmdWxmaWxsZWQgPSBmYWxzZTtcbiBcdFx0XHR9XG4gXHRcdFx0aWYoZnVsZmlsbGVkKSB7XG4gXHRcdFx0XHRkZWZlcnJlZE1vZHVsZXMuc3BsaWNlKGktLSwgMSk7XG4gXHRcdFx0XHRyZXN1bHQgPSBfX3dlYnBhY2tfcmVxdWlyZV9fKF9fd2VicGFja19yZXF1aXJlX18ucyA9IGRlZmVycmVkTW9kdWxlWzBdKTtcbiBcdFx0XHR9XG4gXHRcdH1cblxuIFx0XHRyZXR1cm4gcmVzdWx0O1xuIFx0fVxuXG4gXHQvLyBUaGUgbW9kdWxlIGNhY2hlXG4gXHR2YXIgaW5zdGFsbGVkTW9kdWxlcyA9IHt9O1xuXG4gXHQvLyBvYmplY3QgdG8gc3RvcmUgbG9hZGVkIGFuZCBsb2FkaW5nIGNodW5rc1xuIFx0Ly8gdW5kZWZpbmVkID0gY2h1bmsgbm90IGxvYWRlZCwgbnVsbCA9IGNodW5rIHByZWxvYWRlZC9wcmVmZXRjaGVkXG4gXHQvLyBQcm9taXNlID0gY2h1bmsgbG9hZGluZywgMCA9IGNodW5rIGxvYWRlZFxuIFx0dmFyIGluc3RhbGxlZENodW5rcyA9IHtcbiBcdFx0XCJ0ZXN0XCI6IDBcbiBcdH07XG5cbiBcdHZhciBkZWZlcnJlZE1vZHVsZXMgPSBbXTtcblxuIFx0Ly8gc2NyaXB0IHBhdGggZnVuY3Rpb25cbiBcdGZ1bmN0aW9uIGpzb25wU2NyaXB0U3JjKGNodW5rSWQpIHtcbiBcdFx0cmV0dXJuIF9fd2VicGFja19yZXF1aXJlX18ucCArIFwiXCIgKyAoe1wicHJpbnRcIjpcInByaW50XCJ9W2NodW5rSWRdfHxjaHVua0lkKSArIFwiLmJ1bmRsZS5qc1wiXG4gXHR9XG5cbiBcdC8vIFRoZSByZXF1aXJlIGZ1bmN0aW9uXG4gXHRmdW5jdGlvbiBfX3dlYnBhY2tfcmVxdWlyZV9fKG1vZHVsZUlkKSB7XG5cbiBcdFx0Ly8gQ2hlY2sgaWYgbW9kdWxlIGlzIGluIGNhY2hlXG4gXHRcdGlmKGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdKSB7XG4gXHRcdFx0cmV0dXJuIGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdLmV4cG9ydHM7XG4gXHRcdH1cbiBcdFx0Ly8gQ3JlYXRlIGEgbmV3IG1vZHVsZSAoYW5kIHB1dCBpdCBpbnRvIHRoZSBjYWNoZSlcbiBcdFx0dmFyIG1vZHVsZSA9IGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdID0ge1xuIFx0XHRcdGk6IG1vZHVsZUlkLFxuIFx0XHRcdGw6IGZhbHNlLFxuIFx0XHRcdGV4cG9ydHM6IHt9XG4gXHRcdH07XG5cbiBcdFx0Ly8gRXhlY3V0ZSB0aGUgbW9kdWxlIGZ1bmN0aW9uXG4gXHRcdG1vZHVsZXNbbW9kdWxlSWRdLmNhbGwobW9kdWxlLmV4cG9ydHMsIG1vZHVsZSwgbW9kdWxlLmV4cG9ydHMsIF9fd2VicGFja19yZXF1aXJlX18pO1xuXG4gXHRcdC8vIEZsYWcgdGhlIG1vZHVsZSBhcyBsb2FkZWRcbiBcdFx0bW9kdWxlLmwgPSB0cnVlO1xuXG4gXHRcdC8vIFJldHVybiB0aGUgZXhwb3J0cyBvZiB0aGUgbW9kdWxlXG4gXHRcdHJldHVybiBtb2R1bGUuZXhwb3J0cztcbiBcdH1cblxuIFx0Ly8gVGhpcyBmaWxlIGNvbnRhaW5zIG9ubHkgdGhlIGVudHJ5IGNodW5rLlxuIFx0Ly8gVGhlIGNodW5rIGxvYWRpbmcgZnVuY3Rpb24gZm9yIGFkZGl0aW9uYWwgY2h1bmtzXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLmUgPSBmdW5jdGlvbiByZXF1aXJlRW5zdXJlKGNodW5rSWQpIHtcbiBcdFx0dmFyIHByb21pc2VzID0gW107XG5cblxuIFx0XHQvLyBKU09OUCBjaHVuayBsb2FkaW5nIGZvciBqYXZhc2NyaXB0XG5cbiBcdFx0dmFyIGluc3RhbGxlZENodW5rRGF0YSA9IGluc3RhbGxlZENodW5rc1tjaHVua0lkXTtcbiBcdFx0aWYoaW5zdGFsbGVkQ2h1bmtEYXRhICE9PSAwKSB7IC8vIDAgbWVhbnMgXCJhbHJlYWR5IGluc3RhbGxlZFwiLlxuXG4gXHRcdFx0Ly8gYSBQcm9taXNlIG1lYW5zIFwiY3VycmVudGx5IGxvYWRpbmdcIi5cbiBcdFx0XHRpZihpbnN0YWxsZWRDaHVua0RhdGEpIHtcbiBcdFx0XHRcdHByb21pc2VzLnB1c2goaW5zdGFsbGVkQ2h1bmtEYXRhWzJdKTtcbiBcdFx0XHR9IGVsc2Uge1xuIFx0XHRcdFx0Ly8gc2V0dXAgUHJvbWlzZSBpbiBjaHVuayBjYWNoZVxuIFx0XHRcdFx0dmFyIHByb21pc2UgPSBuZXcgUHJvbWlzZShmdW5jdGlvbihyZXNvbHZlLCByZWplY3QpIHtcbiBcdFx0XHRcdFx0aW5zdGFsbGVkQ2h1bmtEYXRhID0gaW5zdGFsbGVkQ2h1bmtzW2NodW5rSWRdID0gW3Jlc29sdmUsIHJlamVjdF07XG4gXHRcdFx0XHR9KTtcbiBcdFx0XHRcdHByb21pc2VzLnB1c2goaW5zdGFsbGVkQ2h1bmtEYXRhWzJdID0gcHJvbWlzZSk7XG5cbiBcdFx0XHRcdC8vIHN0YXJ0IGNodW5rIGxvYWRpbmdcbiBcdFx0XHRcdHZhciBzY3JpcHQgPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCdzY3JpcHQnKTtcbiBcdFx0XHRcdHZhciBvblNjcmlwdENvbXBsZXRlO1xuXG4gXHRcdFx0XHRzY3JpcHQuY2hhcnNldCA9ICd1dGYtOCc7XG4gXHRcdFx0XHRzY3JpcHQudGltZW91dCA9IDEyMDtcbiBcdFx0XHRcdGlmIChfX3dlYnBhY2tfcmVxdWlyZV9fLm5jKSB7XG4gXHRcdFx0XHRcdHNjcmlwdC5zZXRBdHRyaWJ1dGUoXCJub25jZVwiLCBfX3dlYnBhY2tfcmVxdWlyZV9fLm5jKTtcbiBcdFx0XHRcdH1cbiBcdFx0XHRcdHNjcmlwdC5zcmMgPSBqc29ucFNjcmlwdFNyYyhjaHVua0lkKTtcblxuIFx0XHRcdFx0Ly8gY3JlYXRlIGVycm9yIGJlZm9yZSBzdGFjayB1bndvdW5kIHRvIGdldCB1c2VmdWwgc3RhY2t0cmFjZSBsYXRlclxuIFx0XHRcdFx0dmFyIGVycm9yID0gbmV3IEVycm9yKCk7XG4gXHRcdFx0XHRvblNjcmlwdENvbXBsZXRlID0gZnVuY3Rpb24gKGV2ZW50KSB7XG4gXHRcdFx0XHRcdC8vIGF2b2lkIG1lbSBsZWFrcyBpbiBJRS5cbiBcdFx0XHRcdFx0c2NyaXB0Lm9uZXJyb3IgPSBzY3JpcHQub25sb2FkID0gbnVsbDtcbiBcdFx0XHRcdFx0Y2xlYXJUaW1lb3V0KHRpbWVvdXQpO1xuIFx0XHRcdFx0XHR2YXIgY2h1bmsgPSBpbnN0YWxsZWRDaHVua3NbY2h1bmtJZF07XG4gXHRcdFx0XHRcdGlmKGNodW5rICE9PSAwKSB7XG4gXHRcdFx0XHRcdFx0aWYoY2h1bmspIHtcbiBcdFx0XHRcdFx0XHRcdHZhciBlcnJvclR5cGUgPSBldmVudCAmJiAoZXZlbnQudHlwZSA9PT0gJ2xvYWQnID8gJ21pc3NpbmcnIDogZXZlbnQudHlwZSk7XG4gXHRcdFx0XHRcdFx0XHR2YXIgcmVhbFNyYyA9IGV2ZW50ICYmIGV2ZW50LnRhcmdldCAmJiBldmVudC50YXJnZXQuc3JjO1xuIFx0XHRcdFx0XHRcdFx0ZXJyb3IubWVzc2FnZSA9ICdMb2FkaW5nIGNodW5rICcgKyBjaHVua0lkICsgJyBmYWlsZWQuXFxuKCcgKyBlcnJvclR5cGUgKyAnOiAnICsgcmVhbFNyYyArICcpJztcbiBcdFx0XHRcdFx0XHRcdGVycm9yLm5hbWUgPSAnQ2h1bmtMb2FkRXJyb3InO1xuIFx0XHRcdFx0XHRcdFx0ZXJyb3IudHlwZSA9IGVycm9yVHlwZTtcbiBcdFx0XHRcdFx0XHRcdGVycm9yLnJlcXVlc3QgPSByZWFsU3JjO1xuIFx0XHRcdFx0XHRcdFx0Y2h1bmtbMV0oZXJyb3IpO1xuIFx0XHRcdFx0XHRcdH1cbiBcdFx0XHRcdFx0XHRpbnN0YWxsZWRDaHVua3NbY2h1bmtJZF0gPSB1bmRlZmluZWQ7XG4gXHRcdFx0XHRcdH1cbiBcdFx0XHRcdH07XG4gXHRcdFx0XHR2YXIgdGltZW91dCA9IHNldFRpbWVvdXQoZnVuY3Rpb24oKXtcbiBcdFx0XHRcdFx0b25TY3JpcHRDb21wbGV0ZSh7IHR5cGU6ICd0aW1lb3V0JywgdGFyZ2V0OiBzY3JpcHQgfSk7XG4gXHRcdFx0XHR9LCAxMjAwMDApO1xuIFx0XHRcdFx0c2NyaXB0Lm9uZXJyb3IgPSBzY3JpcHQub25sb2FkID0gb25TY3JpcHRDb21wbGV0ZTtcbiBcdFx0XHRcdGRvY3VtZW50LmhlYWQuYXBwZW5kQ2hpbGQoc2NyaXB0KTtcbiBcdFx0XHR9XG4gXHRcdH1cbiBcdFx0cmV0dXJuIFByb21pc2UuYWxsKHByb21pc2VzKTtcbiBcdH07XG5cbiBcdC8vIGV4cG9zZSB0aGUgbW9kdWxlcyBvYmplY3QgKF9fd2VicGFja19tb2R1bGVzX18pXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLm0gPSBtb2R1bGVzO1xuXG4gXHQvLyBleHBvc2UgdGhlIG1vZHVsZSBjYWNoZVxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5jID0gaW5zdGFsbGVkTW9kdWxlcztcblxuIFx0Ly8gZGVmaW5lIGdldHRlciBmdW5jdGlvbiBmb3IgaGFybW9ueSBleHBvcnRzXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLmQgPSBmdW5jdGlvbihleHBvcnRzLCBuYW1lLCBnZXR0ZXIpIHtcbiBcdFx0aWYoIV9fd2VicGFja19yZXF1aXJlX18ubyhleHBvcnRzLCBuYW1lKSkge1xuIFx0XHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCBuYW1lLCB7IGVudW1lcmFibGU6IHRydWUsIGdldDogZ2V0dGVyIH0pO1xuIFx0XHR9XG4gXHR9O1xuXG4gXHQvLyBkZWZpbmUgX19lc01vZHVsZSBvbiBleHBvcnRzXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLnIgPSBmdW5jdGlvbihleHBvcnRzKSB7XG4gXHRcdGlmKHR5cGVvZiBTeW1ib2wgIT09ICd1bmRlZmluZWQnICYmIFN5bWJvbC50b1N0cmluZ1RhZykge1xuIFx0XHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCBTeW1ib2wudG9TdHJpbmdUYWcsIHsgdmFsdWU6ICdNb2R1bGUnIH0pO1xuIFx0XHR9XG4gXHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCAnX19lc01vZHVsZScsIHsgdmFsdWU6IHRydWUgfSk7XG4gXHR9O1xuXG4gXHQvLyBjcmVhdGUgYSBmYWtlIG5hbWVzcGFjZSBvYmplY3RcbiBcdC8vIG1vZGUgJiAxOiB2YWx1ZSBpcyBhIG1vZHVsZSBpZCwgcmVxdWlyZSBpdFxuIFx0Ly8gbW9kZSAmIDI6IG1lcmdlIGFsbCBwcm9wZXJ0aWVzIG9mIHZhbHVlIGludG8gdGhlIG5zXG4gXHQvLyBtb2RlICYgNDogcmV0dXJuIHZhbHVlIHdoZW4gYWxyZWFkeSBucyBvYmplY3RcbiBcdC8vIG1vZGUgJiA4fDE6IGJlaGF2ZSBsaWtlIHJlcXVpcmVcbiBcdF9fd2VicGFja19yZXF1aXJlX18udCA9IGZ1bmN0aW9uKHZhbHVlLCBtb2RlKSB7XG4gXHRcdGlmKG1vZGUgJiAxKSB2YWx1ZSA9IF9fd2VicGFja19yZXF1aXJlX18odmFsdWUpO1xuIFx0XHRpZihtb2RlICYgOCkgcmV0dXJuIHZhbHVlO1xuIFx0XHRpZigobW9kZSAmIDQpICYmIHR5cGVvZiB2YWx1ZSA9PT0gJ29iamVjdCcgJiYgdmFsdWUgJiYgdmFsdWUuX19lc01vZHVsZSkgcmV0dXJuIHZhbHVlO1xuIFx0XHR2YXIgbnMgPSBPYmplY3QuY3JlYXRlKG51bGwpO1xuIFx0XHRfX3dlYnBhY2tfcmVxdWlyZV9fLnIobnMpO1xuIFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkobnMsICdkZWZhdWx0JywgeyBlbnVtZXJhYmxlOiB0cnVlLCB2YWx1ZTogdmFsdWUgfSk7XG4gXHRcdGlmKG1vZGUgJiAyICYmIHR5cGVvZiB2YWx1ZSAhPSAnc3RyaW5nJykgZm9yKHZhciBrZXkgaW4gdmFsdWUpIF9fd2VicGFja19yZXF1aXJlX18uZChucywga2V5LCBmdW5jdGlvbihrZXkpIHsgcmV0dXJuIHZhbHVlW2tleV07IH0uYmluZChudWxsLCBrZXkpKTtcbiBcdFx0cmV0dXJuIG5zO1xuIFx0fTtcblxuIFx0Ly8gZ2V0RGVmYXVsdEV4cG9ydCBmdW5jdGlvbiBmb3IgY29tcGF0aWJpbGl0eSB3aXRoIG5vbi1oYXJtb255IG1vZHVsZXNcbiBcdF9fd2VicGFja19yZXF1aXJlX18ubiA9IGZ1bmN0aW9uKG1vZHVsZSkge1xuIFx0XHR2YXIgZ2V0dGVyID0gbW9kdWxlICYmIG1vZHVsZS5fX2VzTW9kdWxlID9cbiBcdFx0XHRmdW5jdGlvbiBnZXREZWZhdWx0KCkgeyByZXR1cm4gbW9kdWxlWydkZWZhdWx0J107IH0gOlxuIFx0XHRcdGZ1bmN0aW9uIGdldE1vZHVsZUV4cG9ydHMoKSB7IHJldHVybiBtb2R1bGU7IH07XG4gXHRcdF9fd2VicGFja19yZXF1aXJlX18uZChnZXR0ZXIsICdhJywgZ2V0dGVyKTtcbiBcdFx0cmV0dXJuIGdldHRlcjtcbiBcdH07XG5cbiBcdC8vIE9iamVjdC5wcm90b3R5cGUuaGFzT3duUHJvcGVydHkuY2FsbFxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5vID0gZnVuY3Rpb24ob2JqZWN0LCBwcm9wZXJ0eSkgeyByZXR1cm4gT2JqZWN0LnByb3RvdHlwZS5oYXNPd25Qcm9wZXJ0eS5jYWxsKG9iamVjdCwgcHJvcGVydHkpOyB9O1xuXG4gXHQvLyBfX3dlYnBhY2tfcHVibGljX3BhdGhfX1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5wID0gXCIvZGlzdC9cIjtcblxuIFx0Ly8gb24gZXJyb3IgZnVuY3Rpb24gZm9yIGFzeW5jIGxvYWRpbmdcbiBcdF9fd2VicGFja19yZXF1aXJlX18ub2UgPSBmdW5jdGlvbihlcnIpIHsgY29uc29sZS5lcnJvcihlcnIpOyB0aHJvdyBlcnI7IH07XG5cbiBcdHZhciBqc29ucEFycmF5ID0gd2luZG93W1wid2VicGFja0pzb25wXCJdID0gd2luZG93W1wid2VicGFja0pzb25wXCJdIHx8IFtdO1xuIFx0dmFyIG9sZEpzb25wRnVuY3Rpb24gPSBqc29ucEFycmF5LnB1c2guYmluZChqc29ucEFycmF5KTtcbiBcdGpzb25wQXJyYXkucHVzaCA9IHdlYnBhY2tKc29ucENhbGxiYWNrO1xuIFx0anNvbnBBcnJheSA9IGpzb25wQXJyYXkuc2xpY2UoKTtcbiBcdGZvcih2YXIgaSA9IDA7IGkgPCBqc29ucEFycmF5Lmxlbmd0aDsgaSsrKSB3ZWJwYWNrSnNvbnBDYWxsYmFjayhqc29ucEFycmF5W2ldKTtcbiBcdHZhciBwYXJlbnRKc29ucEZ1bmN0aW9uID0gb2xkSnNvbnBGdW5jdGlvbjtcblxuXG4gXHQvLyBhZGQgZW50cnkgbW9kdWxlIHRvIGRlZmVycmVkIGxpc3RcbiBcdGRlZmVycmVkTW9kdWxlcy5wdXNoKFtcIi4vYnJvd3Nlci9zcmMvdGVzdC5qc1wiLFwidmVuZG9yXCJdKTtcbiBcdC8vIHJ1biBkZWZlcnJlZCBtb2R1bGVzIHdoZW4gcmVhZHlcbiBcdHJldHVybiBjaGVja0RlZmVycmVkTW9kdWxlcygpO1xuIiwiaW1wb3J0IFJlYWN0IGZyb20gJ3JlYWN0JztcblxuY29uc3QgdGhlbWVzID0ge1xuICAgIGxpZ2h0OiB7XG4gICAgICAgIGZvcmVncm91bmQ6IFwiIzAwMDAwMFwiLFxuICAgICAgICBiYWNrZ3JvdW5kOiBcIiNlZWVlZWVcIixcbiAgICAgICAgbmFtZTogJ2xpZ2h0JyxcbiAgICB9LFxuICAgIGRhcms6IHtcbiAgICAgICAgZm9yZWdyb3VuZDogXCIjZmZmZmZmXCIsXG4gICAgICAgIGJhY2tncm91bmQ6IFwiIzIyMjIyMlwiLFxuICAgICAgICBuYW1lOiAnZGFyaycsXG4gICAgfVxufTtcbi8vIENyZWF0ZSBhIGNvbnRleHQgZm9yIHRoZSBjdXJyZW50IHRoZW1lICh3aXRoIFwibGlnaHRcIiBhcyB0aGUgZGVmYXVsdCkuXG5jb25zdCBUaGVtZUNvbnRleHQgPSBSZWFjdC5jcmVhdGVDb250ZXh0KHRoZW1lcy5saWdodCk7XG5mdW5jdGlvbiBUZXN0KCkge1xuICAgIC8vIFVzZSBhIFByb3ZpZGVyIHRvIHBhc3MgdGhlIGN1cnJlbnQgdGhlbWUgdG8gdGhlIHRyZWUgYmVsb3cuXG4gICAgLy8gQW55IGNvbXBvbmVudCBjYW4gcmVhZCBpdCwgbm8gbWF0dGVyIGhvdyBkZWVwIGl0IGlzLlxuICAgIC8vIEluIHRoaXMgZXhhbXBsZSwgd2UncmUgcGFzc2luZyBcImRhcmtcIiBhcyB0aGUgY3VycmVudCB2YWx1ZS5cbiAgICByZXR1cm4gKFxuICAgICAgICA8VGhlbWVDb250ZXh0LlByb3ZpZGVyIHZhbHVlPXt0aGVtZXMuZGFya30+XG4gICAgICAgICAgICA8VG9vbGJhciAvPlxuICAgICAgICA8L1RoZW1lQ29udGV4dC5Qcm92aWRlcj5cbiAgICApO1xufVxuLy8gQSBjb21wb25lbnQgaW4gdGhlIG1pZGRsZSBkb2Vzbid0IGhhdmUgdG9cbi8vIHBhc3MgdGhlIHRoZW1lIGRvd24gZXhwbGljaXRseSBhbnltb3JlLlxuZnVuY3Rpb24gVG9vbGJhcigpIHtcbiAgICByZXR1cm4gKFxuICAgICAgICA8ZGl2PlxuICAgICAgICAgICAgPFRoZW1lZEJ1dHRvbiAvPlxuICAgICAgICA8L2Rpdj5cbiAgICApO1xufVxuZnVuY3Rpb24gVGhlbWVkQnV0dG9uKCkge1xuICAgIC8vIEFzc2lnbiBhIGNvbnRleHRUeXBlIHRvIHJlYWQgdGhlIGN1cnJlbnQgdGhlbWUgY29udGV4dC5cbiAgICAvLyBSZWFjdCB3aWxsIGZpbmQgdGhlIGNsb3Nlc3QgdGhlbWUgUHJvdmlkZXIgYWJvdmUgYW5kIHVzZSBpdHMgdmFsdWUuXG4gICAgLy8gSW4gdGhpcyBleGFtcGxlLCB0aGUgY3VycmVudCB0aGVtZSBpcyBcImRhcmtcIi5cbiAgICBjb25zdCB0aGVtZSA9IFJlYWN0LnVzZUNvbnRleHQoVGhlbWVDb250ZXh0KTtcbiAgICByZXR1cm4gPGJ1dHRvbiBzdHlsZT17eyBiYWNrZ3JvdW5kOiB0aGVtZS5iYWNrZ3JvdW5kLCBjb2xvcjogdGhlbWUuZm9yZWdyb3VuZCB9fT57dGhlbWUubmFtZX08L2J1dHRvbj5cbn1cblxuZXhwb3J0IGRlZmF1bHQgVGVzdDtcbiIsImltcG9ydCBSZWFjdCBmcm9tICdyZWFjdCc7XG5pbXBvcnQgUmVhY3RET00gZnJvbSAncmVhY3QtZG9tJztcblxuLy8gQ29tcG9uZW50cyB0byByZW5kZXJcbmltcG9ydCBUZXN0IGZyb20gJy4vY29tcG9uZW50cy9UZXN0LmpzeCc7XG5cbmltcG9ydCAnLi4vc3R5bGVzL3Rlc3QuY3NzJztcblxuY29uc3QgQ29udGVudCA9ICgpID0+IHtcbiAgICBjb25zdCBbb3Blbiwgc2V0T3Blbl0gPSBSZWFjdC51c2VTdGF0ZSh0cnVlKTtcblxuICAgIHJldHVybiAoXG4gICAgICAgIDw+XG4gICAgICAgICAgICA8aDE+VGVzdCBUZXN0aW5nIFRlc3R5PC9oMT5cbiAgICAgICAgICAgIHtvcGVuICYmXG4gICAgICAgICAgICAgICAgPD5cbiAgICAgICAgICAgICAgICAgICAgPHA+PHNwYW4gY2xhc3NOYW1lPVwiZm9vXCI+TG9yZW0gaXBzdW08L3NwYW4+IGRvbG9yIHNpdCBhbWV0IDxhIGhyZWY9XCIjY29uc2VjdGV0dXJcIj5jb25zZWN0ZXR1ciBhZGlwaXNpY2luZyBlbGl0PC9hPi4gRXZlbmlldCB2b2x1cHRhcyBpbmNpZHVudCBhdHF1ZSBpcHNhbSwgbm9iaXMgcXVpcyBpbnZlbnRvcmUsIHZlbGl0IGxpYmVybyB2ZWwgYXV0ZW0gdGVtcG9yYSwgZnVnaXQgc29sdXRhIGV4Y2VwdHVyaSA8YSBocmVmPVwiI2Zvb1wiPnZvbHVwdGF0dW08L2E+ISBTb2x1dGEgcG9zc2ltdXMgbmloaWwgZG9sb3JlIGhpYy48L3A+XG4gICAgICAgICAgICAgICAgICAgIDxwPkxvcmVtIGlwc3VtIGRvbG9yIHNpdCBhbWV0IGNvbnNlY3RldHVyIGFkaXBpc2ljaW5nIGVsaXQuIEFwZXJpYW0sIHJlcGVsbGVuZHVzIHVsbGFtIGN1bXF1ZSBzZXF1aSBkZXNlcnVudCBjdW0gcG9zc2ltdXMsIGRlbGVuaXRpIGltcGVkaXQgcGFyaWF0dXIgYXRxdWUgZWxpZ2VuZGkuIEVpdXMgZGViaXRpcyBkZWxlY3R1cyBtYXhpbWUgZXNzZSBhLCBvZGlvIHNpbnQgbW9sbGl0aWEhPC9wPlxuICAgICAgICAgICAgICAgICAgICA8cD48YSBocmVmPVwiI2JhclwiPkxvcmVtIGlwc3VtIGRvbG9yIHNpdCBhbWV0IGNvbnNlY3RldHVyPC9hPiwgYWRpcGlzaWNpbmcgZWxpdC4gUXVpcyB0ZW5ldHVyIGZhY2lsaXMgaXBzdW0gZG9sb3JlbXF1ZSBtYWduaSBjdW0uIFByYWVzZW50aXVtIHJlaWNpZW5kaXMgdml0YWUgb21uaXMgZXggc2ludCBlYXF1ZSBlb3MgbmVjZXNzaXRhdGlidXMgYXNzdW1lbmRhIGF0cXVlIHJlcHJlaGVuZGVyaXQsIGNvbW1vZGkgcXVvZC4gTmFtISBMb3JlbSBpcHN1bSBkb2xvciBzaXQgYW1ldCBjb25zZWN0ZXR1ciBhZGlwaXNpY2luZyBlbGl0LiBPYmNhZWNhdGkgY29uc2VjdGV0dXIgc2ltaWxpcXVlIG51bGxhIHZlcml0YXRpcyBhIGltcGVkaXQgcHJvdmlkZW50IGVhcXVlIGRpZ25pc3NpbW9zIGZhY2VyZSBzb2x1dGEgdm9sdXB0YXRlIGFiIGFsaXF1YW0gcXVpZGVtIGN1bHBhIGRvbG9yZXMgaGljIGV4Y2VwdHVyaSwgZWl1cyBxdWFlPzwvcD5cbiAgICAgICAgICAgICAgICAgICAgPHA+TG9yZW0gaXBzdW0gZG9sb3Igc2l0IGFtZXQgY29uc2VjdGV0dXIgYWRpcGlzaWNpbmcgZWxpdC4gQWxpYXMgZmFjZXJlIG1hZ25pIGN1bHBhIG1vbGVzdGlhZSB2b2x1cHRhdGVzIGR1Y2ltdXM/IER1Y2ltdXMgbWludXMgbmVzY2l1bnQgdGVtcG9yYSBhZCBhc3BlcmlvcmVzISBUb3RhbSBhdXRlbSBkb2xvcmUgZW9zIGRlbGVjdHVzIHJlcHJlaGVuZGVyaXQgaXBzYSBhbmltaSBvbW5pcy48L3A+XG4gICAgICAgICAgICAgICAgICAgIDxwPkxvcmVtIGlwc3VtIGRvbG9yIHNpdCwgYW1ldCBjb25zZWN0ZXR1ciBhZGlwaXNpY2luZyBlbGl0LiA8YSBocmVmPVwiI2JhelwiPkl0YXF1ZSBiZWF0YWU8L2E+IGVhcXVlIHByYWVzZW50aXVtIG1vZGkgdm9sdXB0YXRlcyBsaWJlcm8gb2JjYWVjYXRpIGVhcnVtPyBPZmZpY2lhIGltcGVkaXQgZGlzdGluY3RpbyBkZWxlbml0aSBleGVyY2l0YXRpb25lbSBkZWxlY3R1cyEgQXNzdW1lbmRhLCBoaWMgYSBlYXF1ZSBub2JpcyB2ZWxpdCBxdWlzLjwvcD5cbiAgICAgICAgICAgICAgICAgICAgPHA+TG9yZW0gaXBzdW0gZG9sb3Igc2l0LCBhbWV0IGNvbnNlY3RldHVyIGFkaXBpc2ljaW5nIGVsaXQuIEFjY3VzYW11cyBtYWduaSB1dCBhbGlxdWFtIG9mZmljaWlzIG5vc3RydW0gY29uc2VxdWF0dXIgdGVtcG9yZSwgYXQgcmVwdWRpYW5kYWUsIGxhdWRhbnRpdW0gZXhlcmNpdGF0aW9uZW0gaXRhcXVlIGN1bSwgZXQgdm9sdXB0YXRlIHN1c2NpcGl0IG1vZGkgdW5kZSBhZCBkb2xvcmVtcXVlIHNpdCEgTG9yZW0gaXBzdW0gZG9sb3Igc2l0IGFtZXQgY29uc2VjdGV0dXIgYWRpcGlzaWNpbmcgZWxpdC4gQXV0ZW0gbWFpb3JlcyBxdWlzcXVhbSBkaXN0aW5jdGlvIHF1b3MgcXVpIGFkaXBpc2NpIHZvbHVwdGF0ZXMgcGVyZmVyZW5kaXMgb2ZmaWNpYSBjb21tb2RpLCBmdWdpdCBlaXVzIGVzdCB1dCBjb3JydXB0aSByZXByZWhlbmRlcml0IGZ1Z2EgcXVpYnVzZGFtLCBjdW0gaXRhcXVlIHNlcXVpPzwvcD5cbiAgICAgICAgICAgICAgICAgICAgPHA+TG9yZW0gaXBzdW0sIGRvbG9yIHNpdCBhbWV0IGNvbnNlY3RldHVyIGFkaXBpc2ljaW5nIGVsaXQuIEFwZXJpYW0gZGVzZXJ1bnQgZWEgbmF0dXMgaXVzdG8gaXBzYSwgbGFib3JlIGluIGNvbnNlY3RldHVyLCBiZWF0YWUgY29tbW9kaSB2b2x1cHRhcyBoaWMsIHJhdGlvbmUgYXNwZXJpb3JlcyBkaWN0YSBhY2N1c2FudGl1bSBvcHRpbyBxdWFzIHVuZGUgb21uaXMgZXJyb3IhPC9wPlxuICAgICAgICAgICAgICAgICAgICA8cD5Mb3JlbSBpcHN1bSBkb2xvciBzaXQgYW1ldCBjb25zZWN0ZXR1ciBhZGlwaXNpY2luZyBlbGl0LiBJcHNhIG1heGltZSBxdW9kIGV4IGl1cmUgZWl1cyBldCwgc2ludCBkb2xvcmVtcXVlISBMaWJlcm8gZXhlcmNpdGF0aW9uZW0gcGFyaWF0dXIgaGljIGRpZ25pc3NpbW9zLCBkb2xvcnVtIGNvbnNlcXV1bnR1ciBvZGlvIGNvbnNlY3RldHVyIHZvbHVwdGF0ZSBhY2N1c2FtdXMgdm9sdXB0YXRlbSBhLjwvcD5cbiAgICAgICAgICAgICAgICAgICAgPHA+RmluLjwvcD5cbiAgICAgICAgICAgICAgICAgICAgPGgxPiZndDtTVEFSVCBHQU1FIE9wdGlvbnMgRm9vX19fZm9vLmJhcjxiciAvPl9fX19fX19fX19fXyAoTGF5b3V0IGZvbnQpIFBpeGVsIEVtdWxhdG9yPC9oMT5cbiAgICAgICAgICAgICAgICAgICAgPGgxIHN0eWxlPXt7IGZvbnRGYW1pbHk6ICdQcmVzcyBTdGFydCcgfX0+Jmd0O1NUQVJUIEdBTUUgT3B0aW9ucyBGb29fX19mb28uYmFyPGJyIC8+X19fX19fX19fX19fIChNb25vc3BhY2UgZm9udCkgUHJlc3MgU3RhcnQ8L2gxPlxuICAgICAgICAgICAgICAgICAgICA8aDEgc3R5bGU9e3sgZm9udEZhbWlseTogJ1ByZXNzIFN0YXJ0IDJQJyB9fT4mZ3Q7U1RBUlQgR0FNRSBPcHRpb25zIEZvb19fX2Zvby5iYXI8YnIgLz5fX19fX19fX19fX18gUHJlc3MgU3RhcnQgMlA8L2gxPlxuICAgICAgICAgICAgICAgICAgICA8aDEgc3R5bGU9e3sgZm9udEZhbWlseTogJ0VtdWxvZ2ljJyB9fT4mZ3Q7U1RBUlQgR0FNRSBPcHRpb25zIEZvb19fX2Zvby5iYXI8YnIgLz5fX19fX19fX19fX18gRW11bG9naWM8L2gxPlxuICAgICAgICAgICAgICAgICAgICA8aDEgc3R5bGU9e3sgZm9udEZhbWlseTogJ1lvc3RlciBJc2xhbmQnIH19PiZndDtTVEFSVCBHQU1FIE9wdGlvbnMgRm9vX19fZm9vLmJhcjxiciAvPl9fX19fX19fX19fXzwvaDE+XG4gICAgICAgICAgICAgICAgICAgIDxoMSBzdHlsZT17eyBmb250RmFtaWx5OiAnQmMuQk1QMDdfQScgfX0+Jmd0O1NUQVJUIEdBTUUgT3B0aW9ucyBGb29fX19mb28uYmFyPGJyIC8+X19fX19fX19fX19fPC9oMT5cbiAgICAgICAgICAgICAgICAgICAgPGgxIHN0eWxlPXt7IGZvbnRGYW1pbHk6ICdCYy5CTVAwN19LJyB9fT4mZ3Q7U1RBUlQgR0FNRSBPcHRpb25zIEZvb19fX2Zvby5iYXI8YnIgLz5fX19fX19fX19fX188L2gxPlxuICAgICAgICAgICAgICAgICAgICA8aDEgc3R5bGU9e3sgZm9udEZhbWlseTogJ05pbmV0ZWVuTmluZXR5U2V2ZW4nIH19PiZndDtTVEFSVCBHQU1FIE9wdGlvbnMgRm9vX19fZm9vLmJhcjxiciAvPl9fX19fX19fX19fXzwvaDE+XG4gICAgICAgICAgICAgICAgICAgIDxoMSBzdHlsZT17eyBmb250RmFtaWx5OiAnQmFyY2FkZSBCcmF3bCcgfX0+Jmd0O1NUQVJUIEdBTUUgT3B0aW9ucyBGb29fX19mb28uYmFyPGJyIC8+X19fX19fX19fX19fPC9oMT5cbiAgICAgICAgICAgICAgICAgICAgPGgxIHN0eWxlPXt7IGZvbnRGYW1pbHk6ICdCYXJjYWRlIEJyYXdsJyB9fT4mZ3Q7U1RBUlQgR0FNRSBPcHRpb25zIEZvb19fX2Zvby5iYXI8YnIgLz5fX19fX19fX19fX188L2gxPlxuICAgICAgICAgICAgICAgICAgICA8aDEgc3R5bGU9e3sgZm9udEZhbWlseTogJ1N1cGVyIExlZ2VuZCBCb3knIH19PiZndDtTVEFSVCBHQU1FIE9wdGlvbnMgRm9vX19fZm9vLmJhcjxiciAvPl9fX19fX19fX19fXzwvaDE+XG4gICAgICAgICAgICAgICAgPC8+fVxuICAgICAgICAgICAgPGJ1dHRvbiB0eXBlPVwiYnV0dG9uXCIgb25DbGljaz17KCkgPT4gc2V0T3Blbighb3Blbil9PlRvZ2dsZSBmaWxsZXIgdGV4dDwvYnV0dG9uPlxuICAgICAgICAgICAgPHA+RW52PC9wPlxuICAgICAgICAgICAgey8qIFdBUk5JTkc6IFRoZXNlIHZhcmlhYmxlcyB3aWxsIGJlIGV4cG9zZWQgaW4gdGhlIGJ1bmRsZSAqL31cbiAgICAgICAgICAgIDx1bD5cbiAgICAgICAgICAgICAgICA8bGk+RU5WSVJPTk1FTlQ6IHtwcm9jZXNzLmVudi5FTlZJUk9OTUVOVH08L2xpPlxuICAgICAgICAgICAgICAgIDxsaT5IT1NUX0RPTUFJTjoge3Byb2Nlc3MuZW52LkhPU1RfRE9NQUlOfTwvbGk+XG4gICAgICAgICAgICA8L3VsPlxuICAgICAgICAgICAgPGgyPlRlc3Rpbmc8L2gyPlxuICAgICAgICAgICAgPFRlc3QgLz5cbiAgICAgICAgPC8+XG4gICAgKTtcbn07XG5cblJlYWN0RE9NLnJlbmRlcihSZWFjdC5jcmVhdGVFbGVtZW50KENvbnRlbnQpLCBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnY29udGVudCcpKTtcblxuLy8gRGVtb25zdHJhdGVzIGxhenkgbG9hZGluZyBmaWxlc1xuXG5mdW5jdGlvbiBwcmludENvbXBvbmVudCgpIHtcbiAgICBjb25zdCBlbGVtZW50ID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnZGl2Jyk7XG4gICAgY29uc3QgYnV0dG9uID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnYnV0dG9uJyk7XG4gICAgY29uc3QgYnIgPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCdicicpO1xuXG4gICAgYnV0dG9uLmlubmVySFRNTCA9ICdDbGljayBtZSBhbmQgbG9vayBhdCB0aGUgY29uc29sZSEgQnV0IG5vdCBiZWZvcmUgSSBsYXp5IGxvYWQgYSBqcyBjb21wb25lbnQuLi4nO1xuICAgIGVsZW1lbnQuYXBwZW5kQ2hpbGQoYnIpO1xuICAgIGVsZW1lbnQuYXBwZW5kQ2hpbGQoYnV0dG9uKTtcblxuICAgIC8vIE5vdGUgdGhhdCBiZWNhdXNlIGEgbmV0d29yayByZXF1ZXN0IGlzIGludm9sdmVkLCBzb21lIGluZGljYXRpb25cbiAgICAvLyBvZiBsb2FkaW5nIHdvdWxkIG5lZWQgdG8gYmUgc2hvd24gaW4gYSBwcm9kdWN0aW9uLWxldmVsIHNpdGUvYXBwLlxuICAgIGJ1dHRvbi5vbmNsaWNrID0gZSA9PiBpbXBvcnQoLyogd2VicGFja0NodW5rTmFtZTogXCJwcmludFwiICovICcuL2xpYi9wcmludCcpLnRoZW4obW9kdWxlID0+IHtcbiAgICAgICAgLy8gTm90ZSB0aGF0IHdoZW4gdXNpbmcgaW1wb3J0KCkgb24gRVM2IG1vZHVsZXMgeW91IG11c3QgcmVmZXJlbmNlIHRoZSAuZGVmYXVsdCBwcm9wZXJ0eSBhcyBpdCdzIHRoZSBhY3R1YWwgbW9kdWxlIG9iamVjdCB0aGF0IHdpbGwgYmUgcmV0dXJuZWQgd2hlbiB0aGUgcHJvbWlzZSBpcyByZXNvbHZlZC5cbiAgICAgICAgY29uc3QgcHJpbnQgPSBtb2R1bGUuZGVmYXVsdDtcblxuICAgICAgICBwcmludCgpO1xuICAgIH0pO1xuXG4gICAgcmV0dXJuIGVsZW1lbnQ7XG59XG5cbmRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdjb250ZW50JykuYXBwZW5kQ2hpbGQocHJpbnRDb21wb25lbnQoKSk7XG5cbi8vIFJvdXRlclxuXG4vLyBjb25zdCBlbGVtZW50ID0gKFxuLy8gICAgIDw+XG4vLyAgICAgICAgIDxSb3V0ZXI+XG4vLyAgICAgICAgICAgICA8UGFnZSAvPlxuLy8gICAgICAgICA8L1JvdXRlcj5cbi8vICAgICA8Lz5cbi8vICk7XG5cbi8vIFJlYWN0RE9NLnJlbmRlcihlbGVtZW50LCBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncm9vdCcpKTtcbiIsIi8vIGV4dHJhY3RlZCBieSBtaW5pLWNzcy1leHRyYWN0LXBsdWdpblxuZXhwb3J0IHt9OyJdLCJzb3VyY2VSb290IjoiIn0=