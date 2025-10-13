import {
  HighlightStyle,
  syntaxHighlighting,
} from "https://esm.sh/@codemirror/language";
import { EditorView } from "https://esm.sh/@codemirror/view";
import { tags as t } from "https://esm.sh/@lezer/highlight";

const lightTheme = EditorView.theme(
  {
    "&": {
      height: "100%",
    },
    "&.cm-focused": {
      outline: "none",
    },
    ".cm-scroller": {
      color: "#1f2328",
      fontFamily:
        "ui-monospace, SFMono-Regular, SF Mono, Menlo, Consolas, Liberation Mono, monospace",
      fontSize: "14px",
      lineHeight: 1.5,
    },
    ".cm-gutters": {
      backgroundColor: "#ffffff",
      color: "#59636e",
    },
    ".cm-gutters.cm-gutters-before": {
      borderRightWidth: 0,
    },
    ".cm-lineNumbers .cm-gutterElement": {
      padding: "0 0 0 8px",
    },
    ".cm-activeLineGutter": {
      backgroundColor: "#818b9812",
      color: "#1f2328",
    },
    ".cm-foldGutter span": {
      alignItems: "center",
      display: "flex",
      height: "100%",
      padding: "0 4px",
    },
    ".cm-foldGutter span:hover": {
      color: "#1f2328",
    },
    ".cm-content": {
      padding: "8px 0",
    },
    ".cm-line": {
      padding: "0 2px",
    },
    ".cm-activeLine": {
      backgroundColor: "#818b9812",
    },
    "&.cm-focused .cm-matchingBracket": {
      backgroundColor: "#e8f0fe",
      borderRadius: "2px",
      outline: "1px solid #0366d680",
    },
    "&.cm-focused .cm-nonmatchingBracket": {
      backgroundColor: "#ffeef080",
      borderRadius: "2px",
      outline: "1px solid #cb2431",
    },
    ".cm-foldPlaceholder": {
      backgroundColor: "#818b981a",
      border: "none",
      borderRadius: "4px",
      color: "#59636e",
      margin: "0 4px",
      padding: "0 4px",
    },
    ".cm-foldPlaceholder:hover": {
      color: "#0969da",
    },
    ".cm-cursor, .cm-dropCursor": {
      borderLeft: "2px solid #1f2328",
    },
    ".cm-selectionBackground": {
      backgroundColor: "#54aeff66",
    },
    "&.cm-focused > .cm-scroller > .cm-selectionLayer .cm-selectionBackground":
      {
        backgroundColor: "#54aeff66",
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
  { dark: false }
);

const lightHighlightStyle = HighlightStyle.define([
  { tag: t.url, color: "#0a3069", textDecoration: "underline" },
  { tag: t.heading, fontWeight: "bold" },
  { tag: t.emphasis, fontStyle: "italic" },
  { tag: t.strong, fontWeight: "bold" },
  { tag: t.link, textDecoration: "underline" },
  { tag: t.strikethrough, textDecoration: "line-through" },
]);

export const light = [lightTheme, syntaxHighlighting(lightHighlightStyle)];
