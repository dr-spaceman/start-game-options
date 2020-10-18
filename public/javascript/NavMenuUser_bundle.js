(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["NavMenuUser"],{

/***/ "./browser/src/components/Dropdown.jsx":
/*!*********************************************!*\
  !*** ./browser/src/components/Dropdown.jsx ***!
  \*********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! classnames */ "./node_modules/classnames/index.js");
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(classnames__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _lib_match_component_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../lib/match-component.js */ "./browser/src/lib/match-component.js");
/* harmony import */ var _lib_use_outside_click_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../lib/use-outside-click.js */ "./browser/src/lib/use-outside-click.js");
function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }





const isDropdownToggle = Object(_lib_match_component_js__WEBPACK_IMPORTED_MODULE_2__["default"])(DropdownToggle);
const isDropdownMenu = Object(_lib_match_component_js__WEBPACK_IMPORTED_MODULE_2__["default"])(DropdownMenu);

function Dropdown(props) {
  const {
    className,
    children,
    ...rest
  } = props;
  const [open, setOpen] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState(false);

  const handleToggle = () => setOpen(!open);

  const handleClose = () => setOpen(false);

  const classnames = classnames__WEBPACK_IMPORTED_MODULE_1___default()({
    className,
    dropdown: true,
    open
  }); // Event listener is always active... problem?

  const wrapperRef = react__WEBPACK_IMPORTED_MODULE_0___default.a.useRef(null);
  Object(_lib_use_outside_click_js__WEBPACK_IMPORTED_MODULE_3__["default"])(wrapperRef, handleClose);
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", _extends({
    ref: wrapperRef,
    className: classnames
  }, rest), react__WEBPACK_IMPORTED_MODULE_0___default.a.Children.map(children, child => {
    if (! /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.isValidElement(child)) {
      return child;
    } // Button toggle dropdown menu


    if (isDropdownToggle(child)) {
      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.cloneElement(child, {
        handleClick: handleToggle
      });
    }

    return child;
  }));
}

function DropdownToggle({
  className,
  children,
  handleClick
}) {
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    className: `dropdown-toggle ${className}`,
    type: "button",
    onClick: handleClick,
    "aria-haspopup": "true",
    "aria-expanded": "true"
  }, children);
}

function DropdownMenu({
  className,
  children
}) {
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: `dropdown-menu light ${className}`,
    role: "menu"
  }, children);
}

function DropdownItem({
  children
}) {
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: "dropdown-item",
    role: "menuitem"
  }, children);
}

Dropdown.Toggle = DropdownToggle;
Dropdown.Menu = DropdownMenu;
Dropdown.Item = DropdownItem;
/* harmony default export */ __webpack_exports__["default"] = (Dropdown);

/***/ }),

/***/ "./browser/src/components/NavMenuUser.jsx":
/*!************************************************!*\
  !*** ./browser/src/components/NavMenuUser.jsx ***!
  \************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return NavMenuUser; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Dropdown.jsx */ "./browser/src/components/Dropdown.jsx");
/* harmony import */ var _User_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./User.jsx */ "./browser/src/components/User.jsx");



console.log('<NavMenuUser> has been lazy loaded!');
function NavMenuUser({
  username
}) {
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], {
    id: "login-user-dropdown"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Toggle, {
    className: "access-button"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_User_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], {
    username: username,
    href: "",
    avatar: ""
  })), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Menu, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Item, null, "foo"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Item, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: `/~${username}`
  }, "Profile")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Item, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: `/~${username}/games`
  }, "Games")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Item, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "/login.php?do=logout"
  }, "Log out"))));
}

/***/ }),

/***/ "./browser/src/components/User.jsx":
/*!*****************************************!*\
  !*** ./browser/src/components/User.jsx ***!
  \*****************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return User; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);

function User(props) {
  let {
    username,
    avatar,
    href
  } = props;

  if (href === undefined) {
    href = `~${username}`;
  }

  let tag;
  const tagChild = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", {
    className: "user-username"
  }, username);

  if (href) {
    tag = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement('a', {
      href,
      className: 'user-link'
    }, tagChild);
  } else {
    tag = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement('span', {
      className: 'user-link'
    }, tagChild);
  }

  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", {
    className: "user"
  }, tag);
}

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

/***/ "./browser/src/lib/use-outside-click.js":
/*!**********************************************!*\
  !*** ./browser/src/lib/use-outside-click.js ***!
  \**********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return useOutsideClick; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);

/**
 * Hook that alerts clicks outside of the passed ref
 */

function useOutsideClick(ref, callback) {
  react__WEBPACK_IMPORTED_MODULE_0___default.a.useEffect(() => {
    /**
     * Alert if clicked on outside of element
     */
    function handleClickOutside(event) {
      if (ref.current && !ref.current.contains(event.target)) {
        console.log('outside click detected', callback);
        callback();
      }
    } // Bind the event listener


    document.addEventListener('mousedown', handleClickOutside);
    return () => {
      // Clean up before calling the effect again on the next render
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [ref]);
}
/**
 * Example use:
 * Component that alerts if you click outside of it
 */

function MyComponent(props) {
  const wrapperRef = useRef(null);
  useOutsideClick(wrapperRef);
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    ref: wrapperRef
  }, props.children);
}

/***/ })

}]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9jb21wb25lbnRzL0Ryb3Bkb3duLmpzeCIsIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9jb21wb25lbnRzL05hdk1lbnVVc2VyLmpzeCIsIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9jb21wb25lbnRzL1VzZXIuanN4Iiwid2VicGFjazovLy8uL2Jyb3dzZXIvc3JjL2xpYi9tYXRjaC1jb21wb25lbnQuanMiLCJ3ZWJwYWNrOi8vLy4vYnJvd3Nlci9zcmMvbGliL3VzZS1vdXRzaWRlLWNsaWNrLmpzIl0sIm5hbWVzIjpbImlzRHJvcGRvd25Ub2dnbGUiLCJtYXRjaENvbXBvbmVudCIsIkRyb3Bkb3duVG9nZ2xlIiwiaXNEcm9wZG93bk1lbnUiLCJEcm9wZG93bk1lbnUiLCJEcm9wZG93biIsInByb3BzIiwiY2xhc3NOYW1lIiwiY2hpbGRyZW4iLCJyZXN0Iiwib3BlbiIsInNldE9wZW4iLCJSZWFjdCIsInVzZVN0YXRlIiwiaGFuZGxlVG9nZ2xlIiwiaGFuZGxlQ2xvc2UiLCJjbGFzc25hbWVzIiwiY2xhc3NOYW1lcyIsImRyb3Bkb3duIiwid3JhcHBlclJlZiIsInVzZVJlZiIsInVzZU91dHNpZGVDbGljayIsIkNoaWxkcmVuIiwibWFwIiwiY2hpbGQiLCJpc1ZhbGlkRWxlbWVudCIsImNsb25lRWxlbWVudCIsImhhbmRsZUNsaWNrIiwiRHJvcGRvd25JdGVtIiwiVG9nZ2xlIiwiTWVudSIsIkl0ZW0iLCJjb25zb2xlIiwibG9nIiwiTmF2TWVudVVzZXIiLCJ1c2VybmFtZSIsIlVzZXIiLCJhdmF0YXIiLCJocmVmIiwidW5kZWZpbmVkIiwidGFnIiwidGFnQ2hpbGQiLCJjcmVhdGVFbGVtZW50IiwiQ29tcG9uZW50IiwiYyIsInR5cGUiLCJjb21wb25lbnRUeXBlIiwicmVmIiwiY2FsbGJhY2siLCJ1c2VFZmZlY3QiLCJoYW5kbGVDbGlja091dHNpZGUiLCJldmVudCIsImN1cnJlbnQiLCJjb250YWlucyIsInRhcmdldCIsImRvY3VtZW50IiwiYWRkRXZlbnRMaXN0ZW5lciIsInJlbW92ZUV2ZW50TGlzdGVuZXIiLCJNeUNvbXBvbmVudCJdLCJtYXBwaW5ncyI6Ijs7Ozs7Ozs7Ozs7Ozs7Ozs7OztBQUFBO0FBQ0E7QUFDQTtBQUNBO0FBRUEsTUFBTUEsZ0JBQWdCLEdBQUdDLHVFQUFjLENBQUNDLGNBQUQsQ0FBdkM7QUFDQSxNQUFNQyxjQUFjLEdBQUdGLHVFQUFjLENBQUNHLFlBQUQsQ0FBckM7O0FBRUEsU0FBU0MsUUFBVCxDQUFrQkMsS0FBbEIsRUFBeUI7QUFDckIsUUFBTTtBQUFFQyxhQUFGO0FBQWFDLFlBQWI7QUFBdUIsT0FBR0M7QUFBMUIsTUFBbUNILEtBQXpDO0FBRUEsUUFBTSxDQUFDSSxJQUFELEVBQU9DLE9BQVAsSUFBa0JDLDRDQUFLLENBQUNDLFFBQU4sQ0FBZSxLQUFmLENBQXhCOztBQUNBLFFBQU1DLFlBQVksR0FBRyxNQUFNSCxPQUFPLENBQUMsQ0FBQ0QsSUFBRixDQUFsQzs7QUFDQSxRQUFNSyxXQUFXLEdBQUcsTUFBTUosT0FBTyxDQUFDLEtBQUQsQ0FBakM7O0FBRUEsUUFBTUssVUFBVSxHQUFHQyxpREFBVSxDQUFDO0FBQzFCVixhQUQwQjtBQUUxQlcsWUFBUSxFQUFFLElBRmdCO0FBRzFCUjtBQUgwQixHQUFELENBQTdCLENBUHFCLENBYXJCOztBQUNBLFFBQU1TLFVBQVUsR0FBR1AsNENBQUssQ0FBQ1EsTUFBTixDQUFhLElBQWIsQ0FBbkI7QUFDQUMsMkVBQWUsQ0FBQ0YsVUFBRCxFQUFhSixXQUFiLENBQWY7QUFFQSxzQkFDSTtBQUFLLE9BQUcsRUFBRUksVUFBVjtBQUFzQixhQUFTLEVBQUVIO0FBQWpDLEtBQWlEUCxJQUFqRCxHQUVLRyw0Q0FBSyxDQUFDVSxRQUFOLENBQWVDLEdBQWYsQ0FBbUJmLFFBQW5CLEVBQThCZ0IsS0FBRCxJQUFXO0FBQ3JDLFFBQUksZUFBQ1osNENBQUssQ0FBQ2EsY0FBTixDQUFxQkQsS0FBckIsQ0FBTCxFQUFrQztBQUM5QixhQUFPQSxL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QUNDLGdCQUFULENBQTBCLFdBQTFCLEVBQXVDTixrQkFBdkM7QUFDQSxXQUFPLE1BQU07QUFDVDtBQUNBSyxjQUFRLENBQUNFLG1CQUFULENBQTZCLFdBQTdCLEVBQTBDUCxrQkFBMUM7QUFDSCxLQUhEO0FBSUgsR0FqQkQsRUFpQkcsQ0FBQ0gsR0FBRCxDQWpCSDtBQWtCSDtBQUVEOzs7OztBQUlBLFNBQVNXLFdBQVQsQ0FBcUJwRCxLQUFyQixFQUE0QjtBQUN4QixRQUFNYSxVQUFVLEdBQUdDLE1BQU0sQ0FBQyxJQUFELENBQXpCO0FBQ0FDLGlCQUFlLENBQUNGLFVBQUQsQ0FBZjtBQUVBLHNCQUFPO0FBQUssT0FBRyxFQUFFQTtBQUFWLEtBQXVCYixLQUFLLENBQUNFLFFBQTdCLENBQVA7QUFDSCxDIiwiZmlsZSI6Ik5hdk1lbnVVc2VyX2J1bmRsZS5qcyIsInNvdXJjZXNDb250ZW50IjpbImltcG9ydCBSZWFjdCBmcm9tICdyZWFjdCc7XG5pbXBvcnQgY2xhc3NOYW1lcyBmcm9tICdjbGFzc25hbWVzJztcbmltcG9ydCBtYXRjaENvbXBvbmVudCBmcm9tICcuLi9saWIvbWF0Y2gtY29tcG9uZW50LmpzJztcbmltcG9ydCB1c2VPdXRzaWRlQ2xpY2sgZnJvbSAnLi4vbGliL3VzZS1vdXRzaWRlLWNsaWNrLmpzJztcblxuY29uc3QgaXNEcm9wZG93blRvZ2dsZSA9IG1hdGNoQ29tcG9uZW50KERyb3Bkb3duVG9nZ2xlKTtcbmNvbnN0IGlzRHJvcGRvd25NZW51ID0gbWF0Y2hDb21wb25lbnQoRHJvcGRvd25NZW51KTtcblxuZnVuY3Rpb24gRHJvcGRvd24ocHJvcHMpIHtcbiAgICBjb25zdCB7IGNsYXNzTmFtZSwgY2hpbGRyZW4sIC4uLnJlc3QgfSA9IHByb3BzO1xuXG4gICAgY29uc3QgW29wZW4sIHNldE9wZW5dID0gUmVhY3QudXNlU3RhdGUoZmFsc2UpO1xuICAgIGNvbnN0IGhhbmRsZVRvZ2dsZSA9ICgpID0+IHNldE9wZW4oIW9wZW4pO1xuICAgIGNvbnN0IGhhbmRsZUNsb3NlID0gKCkgPT4gc2V0T3BlbihmYWxzZSk7XG5cbiAgICBjb25zdCBjbGFzc25hbWVzID0gY2xhc3NOYW1lcyh7XG4gICAgICAgIGNsYXNzTmFtZSxcbiAgICAgICAgZHJvcGRvd246IHRydWUsXG4gICAgICAgIG9wZW4sXG4gICAgfSk7XG5cbiAgICAvLyBFdmVudCBsaXN0ZW5lciBpcyBhbHdheXMgYWN0aXZlLi4uIHByb2JsZW0/XG4gICAgY29uc3Qgd3JhcHBlclJlZiA9IFJlYWN0LnVzZVJlZihudWxsKTtcbiAgICB1c2VPdXRzaWRlQ2xpY2sod3JhcHBlclJlZiwgaGFuZGxlQ2xvc2UpO1xuXG4gICAgcmV0dXJuIChcbiAgICAgICAgPGRpdiByZWY9e3dyYXBwZXJSZWZ9IGNsYXNzTmFtZT17Y2xhc3NuYW1lc30gey4uLnJlc3R9PlxuICAgICAgICAgICAgey8qIE1hcCBjaGlsZHJlbiAmIGluamVjdCBsaXN0ZW5lcnMgKi99XG4gICAgICAgICAgICB7UmVhY3QuQ2hpbGRyZW4ubWFwKGNoaWxkcmVuLCAoY2hpbGQpID0+IHtcbiAgICAgICAgICAgICAgICBpZiAoIVJlYWN0LmlzVmFsaWRFbGVtZW50KGNoaWxkKSkge1xuICAgICAgICAgICAgICAgICAgICByZXR1cm4gY2hpbGQ7XG4gICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgLy8gQnV0dG9uIHRvZ2dsZSBkcm9wZG93biBtZW51XG4gICAgICAgICAgICAgICAgaWYgKGlzRHJvcGRvd25Ub2dnbGUoY2hpbGQpKSB7XG4gICAgICAgICAgICAgICAgICAgIHJldHVybiBSZWFjdC5jbG9uZUVsZW1lbnQoY2hpbGQsIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIGhhbmRsZUNsaWNrOiBoYW5kbGVUb2dnbGUsXG4gICAgICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgICAgIHJldHVybiBjaGlsZDtcbiAgICAgICAgICAgIH0pfVxuICAgICAgICA8L2Rpdj5cbiAgICApO1xufVxuXG5mdW5jdGlvbiBEcm9wZG93blRvZ2dsZSh7IGNsYXNzTmFtZSwgY2hpbGRyZW4sIGhhbmRsZUNsaWNrIH0pIHtcbiAgICByZXR1cm4gKFxuICAgICAgICA8YnV0dG9uIGNsYXNzTmFtZT17YGRyb3Bkb3duLXRvZ2dsZSAke2NsYXNzTmFtZX1gfSB0eXBlPVwiYnV0dG9uXCIgb25DbGljaz17aGFuZGxlQ2xpY2t9IGFyaWEtaGFzcG9wdXA9XCJ0cnVlXCIgYXJpYS1leHBhbmRlZD1cInRydWVcIj5cbiAgICAgICAgICAgIHtjaGlsZHJlbn1cbiAgICAgICAgPC9idXR0b24+XG4gICAgKTtcbn1cblxuZnVuY3Rpb24gRHJvcGRvd25NZW51KHsgY2xhc3NOYW1lLCBjaGlsZHJlbiB9KSB7XG4gICAgcmV0dXJuIChcbiAgICAgICAgPGRpdiBjbGFzc05hbWU9e2Bkcm9wZG93bi1tZW51IGxpZ2h0ICR7Y2xhc3NOYW1lfWB9IHJvbGU9XCJtZW51XCI+XG4gICAgICAgICAgICB7Y2hpbGRyZW59XG4gICAgICAgIDwvZGl2PlxuICAgICk7XG59XG5cbmZ1bmN0aW9uIERyb3Bkb3duSXRlbSh7IGNoaWxkcmVuIH0pIHtcbiAgICByZXR1cm4gKFxuICAgICAgICA8ZGl2IGNsYXNzTmFtZT1cImRyb3Bkb3duLWl0ZW1cIiByb2xlPVwibWVudWl0ZW1cIj57Y2hpbGRyZW59PC9kaXY+XG4gICAgKTtcbn1cblxuRHJvcGRvd24uVG9nZ2xlID0gRHJvcGRvd25Ub2dnbGU7XG5Ecm9wZG93bi5NZW51ID0gRHJvcGRvd25NZW51O1xuRHJvcGRvd24uSXRlbSA9IERyb3Bkb3duSXRlbTtcblxuZXhwb3J0IGRlZmF1bHQgRHJvcGRvd247XG4iLCJpbXBvcnQgUmVhY3QgZnJvbSAncmVhY3QnO1xuaW1wb3J0IERyb3Bkb3duIGZyb20gJy4vRHJvcGRvd24uanN4JztcbmltcG9ydCBVc2VyIGZyb20gJy4vVXNlci5qc3gnO1xuXG5jb25zb2xlLmxvZygnPE5hdk1lbnVVc2VyPiBoYXMgYmVlbiBsYXp5IGxvYWRlZCEnKTtcblxuZXhwb3J0IGRlZmF1bHQgZnVuY3Rpb24gTmF2TWVudVVzZXIoeyB1c2VybmFtZSB9KSB7XG4gICAgcmV0dXJuIChcbiAgICAgICAgPERyb3Bkb3duIGlkPVwibG9naW4tdXNlci1kcm9wZG93blwiPlxuICAgICAgICAgICAgPERyb3Bkb3duLlRvZ2dsZSBjbGFzc05hbWU9XCJhY2Nlc3MtYnV0dG9uXCI+XG4gICAgICAgICAgICAgICAgPFVzZXIgdXNlcm5hbWU9e3VzZXJuYW1lfSBocmVmPVwiXCIgYXZhdGFyPVwiXCIgLz5cbiAgICAgICAgICAgIDwvRHJvcGRvd24uVG9nZ2xlPlxuICAgICAgICAgICAgPERyb3Bkb3duLk1lbnU+XG4gICAgICAgICAgICAgICAgPERyb3Bkb3duLkl0ZW0+Zm9vPC9Ecm9wZG93bi5JdGVtPlxuICAgICAgICAgICAgICAgIDxEcm9wZG93bi5JdGVtPjxhIGhyZWY9e2AvfiR7dXNlcm5hbWV9YH0+UHJvZmlsZTwvYT48L0Ryb3Bkb3duLkl0ZW0+XG4gICAgICAgICAgICAgICAgPERyb3Bkb3duLkl0ZW0+PGEgaHJlZj17YC9+JHt1c2VybmFtZX0vZ2FtZXNgfT5HYW1lczwvYT48L0Ryb3Bkb3duLkl0ZW0+XG4gICAgICAgICAgICAgICAgPERyb3Bkb3duLkl0ZW0+PGEgaHJlZj1cIi9sb2dpbi5waHA/ZG89bG9nb3V0XCI+TG9nIG91dDwvYT48L0Ryb3Bkb3duLkl0ZW0+XG4gICAgICAgICAgICA8L0Ryb3Bkb3duLk1lbnU+XG4gICAgICAgIDwvRHJvcGRvd24+XG4gICAgKTtcbn1cbiIsImltcG9ydCBSZWFjdCBmcm9tICdyZWFjdCc7XG5cbmV4cG9ydCBkZWZhdWx0IGZ1bmN0aW9uIFVzZXIocHJvcHMpIHtcbiAgICBsZXQgeyB1c2VybmFtZSwgYXZhdGFyLCBocmVmIH0gPSBwcm9wcztcblxuICAgIGlmIChocmVmID09PSB1bmRlZmluZWQpIHtcbiAgICAgICAgaHJlZiA9IGB+JHt1c2VybmFtZX1gO1xuICAgIH1cblxuICAgIGxldCB0YWc7XG4gICAgY29uc3QgdGFnQ2hpbGQgPSA8c3BhbiBjbGFzc05hbWU9XCJ1c2VyLXVzZXJuYW1lXCI+e3VzZXJuYW1lfTwvc3Bhbj47XG4gICAgaWYgKGhyZWYpIHtcbiAgICAgICAgdGFnID0gUmVhY3QuY3JlYXRlRWxlbWVudCgnYScsIHsgaHJlZiwgY2xhc3NOYW1lOiAndXNlci1saW5rJyB9LCB0YWdDaGlsZCk7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgdGFnID0gUmVhY3QuY3JlYXRlRWxlbWVudCgnc3BhbicsIHsgY2xhc3NOYW1lOiAndXNlci1saW5rJyB9LCB0YWdDaGlsZCk7XG4gICAgfVxuXG4gICAgcmV0dXJuIChcbiAgICAgICAgPHNwYW4gY2xhc3NOYW1lPVwidXNlclwiPlxuICAgICAgICAgICAge3RhZ31cbiAgICAgICAgPC9zcGFuPlxuICAgICk7XG59XG4iLCJjb25zdCBtYXRjaENvbXBvbmVudCA9IChDb21wb25lbnQpID0+IChjKSA9PiB7XHJcbiAgICAvLyBSZWFjdCBDb21wb25lbnRcclxuICAgIGlmIChjLnR5cGUgPT09IENvbXBvbmVudCkge1xyXG4gICAgICAgIHJldHVybiB0cnVlO1xyXG4gICAgfVxyXG5cclxuICAgIC8vIE1hdGNoaW5nIGNvbXBvbmVudFR5cGVcclxuICAgIGlmIChjLnByb3BzICYmIGMucHJvcHMuY29tcG9uZW50VHlwZSA9PT0gQ29tcG9uZW50KSB7XHJcbiAgICAgICAgcmV0dXJuIHRydWU7XHJcbiAgICB9XHJcblxyXG4gICAgcmV0dXJuIGZhbHNlO1xyXG59O1xyXG5cclxuZXhwb3J0IGRlZmF1bHQgbWF0Y2hDb21wb25lbnQ7IiwiaW1wb3J0IFJlYWN0IGZyb20gJ3JlYWN0JztcblxuLyoqXG4gKiBIb29rIHRoYXQgYWxlcnRzIGNsaWNrcyBvdXRzaWRlIG9mIHRoZSBwYXNzZWQgcmVmXG4gKi9cbmV4cG9ydCBkZWZhdWx0IGZ1bmN0aW9uIHVzZU91dHNpZGVDbGljayhyZWYsIGNhbGxiYWNrKSB7XG4gICAgUmVhY3QudXNlRWZmZWN0KCgpID0+IHtcbiAgICAgICAgLyoqXG4gICAgICAgICAqIEFsZXJ0IGlmIGNsaWNrZWQgb24gb3V0c2lkZSBvZiBlbGVtZW50XG4gICAgICAgICAqL1xuICAgICAgICBmdW5jdGlvbiBoYW5kbGVDbGlja091dHNpZGUoZXZlbnQpIHtcbiAgICAgICAgICAgIGlmIChyZWYuY3VycmVudCAmJiAhcmVmLmN1cnJlbnQuY29udGFpbnMoZXZlbnQudGFyZ2V0KSkge1xuICAgICAgICAgICAgICAgIGNvbnNvbGUubG9nKCdvdXRzaWRlIGNsaWNrIGRldGVjdGVkJywgY2FsbGJhY2spO1xuICAgICAgICAgICAgICAgIGNhbGxiYWNrKCk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cblxuICAgICAgICAvLyBCaW5kIHRoZSBldmVudCBsaXN0ZW5lclxuICAgICAgICBkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCdtb3VzZWRvd24nLCBoYW5kbGVDbGlja091dHNpZGUpO1xuICAgICAgICByZXR1cm4gKCkgPT4ge1xuICAgICAgICAgICAgLy8gQ2xlYW4gdXAgYmVmb3JlIGNhbGxpbmcgdGhlIGVmZmVjdCBhZ2FpbiBvbiB0aGUgbmV4dCByZW5kZXJcbiAgICAgICAgICAgIGRvY3VtZW50LnJlbW92ZUV2ZW50TGlzdGVuZXIoJ21vdXNlZG93bicsIGhhbmRsZUNsaWNrT3V0c2lkZSk7XG4gICAgICAgIH07XG4gICAgfSwgW3JlZl0pO1xufVxuXG4vKipcbiAqIEV4YW1wbGUgdXNlOlxuICogQ29tcG9uZW50IHRoYXQgYWxlcnRzIGlmIHlvdSBjbGljayBvdXRzaWRlIG9mIGl0XG4gKi9cbmZ1bmN0aW9uIE15Q29tcG9uZW50KHByb3BzKSB7XG4gICAgY29uc3Qgd3JhcHBlclJlZiA9IHVzZVJlZihudWxsKTtcbiAgICB1c2VPdXRzaWRlQ2xpY2sod3JhcHBlclJlZik7XG5cbiAgICByZXR1cm4gPGRpdiByZWY9e3dyYXBwZXJSZWZ9Pntwcm9wcy5jaGlsZHJlbn08L2Rpdj47XG59XG4iXSwic291cmNlUm9vdCI6IiJ9