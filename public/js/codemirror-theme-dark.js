import {
  HighlightStyle,
  syntaxHighlighting,
} from "https://esm.sh/@codemirror/language";
import { EditorView } from "https://esm.sh/@codemirror/view";
import { tags as t } from "https://esm.sh/@lezer/highlight";

const darkTheme = EditorView.theme(
  {
    "&": {
      height: "100%",
    },
    "&.cm-focused": {
      outline: "none",
    },
    ".cm-scroller": {
      color: "#f0f6fc",
      fontFamily:
        "ui-monospace, SFMono-Regular, SF Mono, Menlo, Consolas, Liberation Mono, monospace",
      fontSize: "14px",
      lineHeight: 1.5,
    },
    ".cm-gutters": {
      backgroundColor: "#0d1117",
      color: "#9198a1",
    },
    ".cm-gutters.cm-gutters-before": {
      borderRightWidth: 0,
    },
    ".cm-lineNumbers .cm-gutterElement": {
      padding: "0 0 0 8px",
    },
    ".cm-activeLineGutter": {
      backgroundColor: "#656c761f",
      color: "#f0f6fc",
    },
    ".cm-foldGutter span": {
      alignItems: "center",
      display: "flex",
      height: "100%",
      padding: "0 4px",
    },
    ".cm-foldGutter span:hover": {
      color: "#f0f6fc",
    },
    ".cm-content": {
      padding: "8px 0",
    },
    ".cm-line": {
      padding: "0 2px",
    },
    ".cm-activeLine": {
      backgroundColor: "#656c761f",
    },
    "&.cm-focused .cm-matchingBracket": {
      backgroundColor: "#3a587a75",
      borderRadius: "2px",
      outline: "1px solid #4d90fe",
    },
    "&.cm-focused .cm-nonmatchingBracket": {
      backgroundColor: "#f9758340",
      borderRadius: "2px",
      outline: "1px solid #f97583",
    },
    ".cm-foldPlaceholder": {
      backgroundColor: "#656c7633",
      border: "none",
      borderRadius: "4px",
      color: "#9198a1",
      margin: "0 4px",
      padding: "0 4px",
    },
    ".cm-foldPlaceholder:hover": {
      color: "#4493f8",
    },
    ".cm-cursor, .cm-dropCursor": {
      borderLeft: "2px solid #f0f6fc",
    },
    ".cm-selectionBackground": {
      backgroundColor: "#1f6febb3",
    },
    "&.cm-focused > .cm-scroller > .cm-selectionLayer .cm-selectionBackground":
      {
        backgroundColor: "#1f6febb3",
      },
    "@supports (-webkit-touch-callout: none)": {
      ".cm-scroller": {
        fontSize: "16px",
      },
      "@media (min-width: 768px)": {
        ".cm-scroller": {
          fontSize: "14px",
        },
      },
    },
  },
  { dark: true }
);

const darkHighlightStyle = HighlightStyle.define([
  { tag: t.url, color: "#a5d6ff", textDecoration: "underline" },
  { tag: t.heading, fontWeight: "bold" },
  { tag: t.emphasis, fontStyle: "italic" },
  { tag: t.strong, fontWeight: "bold" },
  { tag: t.link, textDecoration: "underline" },
  { tag: t.strikethrough, textDecoration: "line-through" },
]);

export const dark = [darkTheme, syntaxHighlighting(darkHighlightStyle)];
