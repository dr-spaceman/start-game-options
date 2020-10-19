import React from 'react';

/**
 * Hook that listens for clicks outside of a reference.
 *
 * @param {Function} callback Function to execute on successful outside click
 *
 * @returns {Reference} Reference to "inside" element
 */
export default function useOutsideClick(callback) {
    const ref = React.useRef();

    function handleClickOutside(event) {
        // If the click is registered outside the given ref, trigger cb
        if (!ref.current?.contains?.(event.target)) {
            console.log('Outside click detected', callback);
            callback(event);
        }
    }

    React.useEffect(() => {
        // Bind the event listener
        document.addEventListener('click', handleClickOutside);
        console.log('Listening for outside click...');
        return function cleanup() {
            // Clean up before calling the effect again on the next render
            document.removeEventListener('click', handleClickOutside);
            console.log('Remove outside click handler');
        };
    }, [ref]);

    return ref;
}

function useOutsideClick_OLD(ref, callback = () => {}) {
    function handleClickOutside(event) {
        // If the click is registered outside the given ref, trigger cb
        if (!ref.current?.contains?.(event.target)) {
            console.log('Outside click detected', callback);
            callback(event);
        }
    }

    React.useEffect(() => {
        // Bind the event listener
        document.addEventListener('click', handleClickOutside);
        console.log('Listening for outside click...');
        return () => {
            // Clean up before calling the effect again on the next render
            document.removeEventListener('click', handleClickOutside);
            console.log('Remove outside click handler');
        };
    }, [callback]);
}
