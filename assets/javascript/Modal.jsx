import React from 'react';
import { CSSTransition } from 'react-transition-group';

export default function Modal(props) {
    const { children, open = true, close = null, timeout = 500, overlay = true } = props;

    return (
        <CSSTransition in={open} timeout={timeout} classNames="modal" unmountOnExit>
            <div className="modal modal-container">
                {overlay && <div className="modal-overlay" role="button" onClick={close} aria-hidden="true" aria-label="close" />}
                <div className="modal-content">
                    {children}
                </div>
            </div>
        </CSSTransition>
    );
}
