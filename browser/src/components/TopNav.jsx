import React from 'react';
import classNames from 'classnames';
import { CSSTransition } from 'react-transition-group';
import NavMenu from './NavMenu.jsx';

export default function TopNav() {
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
            <NavMenu>
                <NavMenu.Item selected>
                    <h6><a href="/games">Start Game</a></h6>
                </NavMenu.Item>
                <NavMenu.Item>
                    {/* accessibilize */}
                    <button type="button" role="switch" aria-checked={open} id="menu" className={buttonClasses} onClick={toggleOpen}>
                        Options
                    </button>
                </NavMenu.Item>
                <NavMenu.Item className="hidden"><a href="/games">Games</a></NavMenu.Item>
                <NavMenu.Item className="hidden"><a href="/people">People</a></NavMenu.Item>
                <NavMenu.Item className="hidden"><a href="/music">Music</a></NavMenu.Item>
            </NavMenu>
        </CSSTransition>
    );
}
