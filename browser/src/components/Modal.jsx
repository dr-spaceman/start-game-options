import React from 'react';
import { CSSTransition } from 'react-transition-group';
import { BiX } from 'react-icons/bi';

/**
 * 
 * @param {Boolean} props 
 */
export default function Modal(props) {
    const {
        children,
        open = true,
        close = null,
        timeout = 500,
        overlay = true,
        closeButton = true,
    } = props;

    const CloseButton = () => closeButton && (
        <button type="button" role="switch" aria-checked={open} aria-label="Close" className="modal-close close-button" onClick={close}>
            <BiX arial-hidden="true" />
        </button>
    );
    const Overlay = () => overlay && (
        <div className="modal-overlay" role="button" onClick={close} aria-hidden="true" aria-label="close" />
    );

    return (
        <CSSTransition in={open} timeout={timeout} classNames="modal" unmountOnExit>
            <div className="modal modal-container">
                <Overlay />
                <div className="modal-content light">
                    {children}
                    <CloseButton />
                </div>
            </div>
        </CSSTransition>
    );
}
