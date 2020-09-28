import React from 'react';
import classNames from 'classnames';
import { CSSTransition } from 'react-transition-group';

import Modal from './Modal.jsx';

export default function NavMenu(props) {
    const [open, setOpen] = React.useState(false);
    const toggleOpen = () => {
        setOpen(!open);
    };

    const buttonClasses = classNames({
        'access-button': true,
        active: open,
        inactive: !open,
    });

    return (
        <CSSTransition in={open} timeout={1500}>
            <nav id="navmenu">
                <ul>
                    <li className="navmenu-item-container">
                        <h6><a href="/games">Start Game</a></h6>
                    </li>
                    <li className="navmenu-item-container">
                        {/* accessibilize */}
                        <button type="button" role="switch" aria-checked={open} id="menu" className={buttonClasses} onClick={toggleOpen}>
                            Options
                        </button>
                    </li>
                    <li className="navmenu-item-container hidden"><a href="/games">Games</a></li>
                    <li className="navmenu-item-container hidden"><a href="/people">People</a></li>
                    <li className="navmenu-item-container hidden"><a href="/music">Music</a></li>
                </ul>
            </nav>
        </CSSTransition>
    );
}
