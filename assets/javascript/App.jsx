import React from 'react';
import ReactDOM from 'react-dom';

// Components to render
import Search from './Search.jsx';
import Colophon from './Colophon.jsx';

// Stylesheets that get injected into <head>
import 'normalize.css';
import '../styles/app.scss';

ReactDOM.render(
    React.createElement(Search),
    document.getElementById('search'),
);

ReactDOM.render(
    React.createElement(Colophon),
    document.getElementById('colophon'),
);

// const element = (
//     <>
//         <Router>
//             <Page />
//         </Router>
//     </>
// );

// ReactDOM.render(element, document.getElementById('root'));

// Hot Module Replacement
if (module.hot) {
    module.hot.accept();
}
