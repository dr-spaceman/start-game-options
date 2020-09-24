import React from 'react';
import { CSSTransition } from 'react-transition-group';

export default function Modal(props) {
    const { children, open, close, timeout = 500, overlay = true } = props;

    return (
        <CSSTransition in={open} timeout={timeout} classNames="modal" unmountOnExit>
            <div className="modal-container">
                {overlay && <div className="modal-overlay" role="button" onClick={close} aria-hidden="true" aria-label="close" />}
                <div className="modal dark">
                    {children}
                </div>
            </div>
        </CSSTransition>
    );
}
